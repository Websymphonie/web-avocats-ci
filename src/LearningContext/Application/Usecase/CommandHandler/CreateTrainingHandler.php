<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\CreateTrainingCommand;
use Websymphonie\LearningContext\Application\Service\YouTubeSourceParser;
use Websymphonie\LearningContext\Application\Service\TrainingClassificationValidator;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\TrainingSlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateTrainingHandler implements CommandHandler
{
    public function __construct(
        private TrainingRepositoryInterface $repository,
        private RichTextSanitizerInterface $sanitizer,
        private SluggerInterface $slugger,
        private MediaUploadServiceInterface $mediaUpload,
        private TrainingClassificationValidator $classification,
        private YouTubeSourceParser $videoParser,
    ) {}

    public function __invoke(CreateTrainingCommand $command): Training
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) {
            throw TrainingSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        [$categoryIds, $tagIds] = $this->classification->validate($command->categoryIds, $command->tagIds);
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'training/covers') : null;
            $liveDetails = null;
            if ($command->type === TrainingType::LIVE) {
                $liveSource = $this->videoParser->parseLiveReference($command->liveVideoReferenceUrl);
                $replaySource = $this->videoParser->parseLiveReference($command->replayVideoReferenceUrl);
                $liveDetails = new LiveTrainingDetails(
                    id: 0,
                    uuid: '',
                    trainingId: 0,
                    startsAt: $command->startsAt ?? throw new InvalidLiveTrainingDetailsException('La date de début est requise pour un LIVE.'),
                    endsAt: $command->endsAt ?? throw new InvalidLiveTrainingDetailsException('La date de fin est requise pour un LIVE.'),
                    deliveryMode: $command->deliveryMode,
                    location: self::clean($command->location),
                    joinUrl: self::clean($command->joinUrl),
                    liveSource: $liveSource,
                    replaySource: $replaySource,
                );
            }
            $training = new Training(
                id: 0,
                uuid: '',
                title: trim($command->title),
                slug: $slug,
                summary: trim($command->summary),
                description: $this->sanitizer->sanitize($command->description),
                visibility: $command->visibility,
                accessType: $command->accessType,
                type: $command->type,
                coverMediaId: $media?->id,
                categoryIds: $categoryIds,
                tagIds: $tagIds,
                liveDetails: $liveDetails,
            );

            return $this->repository->save($training);
        } catch (\Throwable $exception) {
            if ($media !== null) {
                try { $this->mediaUpload->delete($media); } catch (\Throwable) {}
            }
            throw $exception;
        }
    }

    private static function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;
        return $value === '' ? null : $value;
    }

}
