<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\UpdateTrainingCommand;
use Websymphonie\LearningContext\Application\Service\YouTubeSourceParser;
use Websymphonie\LearningContext\Application\Service\TrainingClassificationValidator;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Exception\TrainingSlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateTrainingHandler implements CommandHandler
{
    public function __construct(
        private TrainingRepositoryInterface $repository,
        private RichTextSanitizerInterface $sanitizer,
        private SluggerInterface $slugger,
        private MediaUploadServiceInterface $mediaUpload,
        private MediaRepositoryInterface $mediaRepository,
        private TrainingClassificationValidator $classification,
        private YouTubeSourceParser $videoParser,
    ) {}

    public function __invoke(UpdateTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        [$categoryIds, $tagIds] = $this->classification->validate($command->categoryIds, $command->tagIds);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($training->status === TrainingStatus::DRAFT && ($slug === '' || $this->repository->slugExists($slug, $training->id))) {
            throw TrainingSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        $training->update(
            title: trim($command->title),
            slug: $slug ?: $training->slug,
            summary: trim($command->summary),
            description: $this->sanitizer->sanitize($command->description),
            visibility: $command->visibility,
            accessType: $command->accessType,
        );
        $training->replaceClassification($categoryIds, $tagIds);

        if ($training->type === TrainingType::LIVE) {
            $liveSource = $this->videoParser->parseLiveReference($command->liveVideoReference, $command->liveVideoProvider);
            $replaySource = $this->videoParser->parseLiveReference($command->replayVideoReference, $command->replayVideoProvider);
            $details = $training->liveDetails ?? new LiveTrainingDetails(
                id: 0,
                uuid: '',
                trainingId: $training->id,
                startsAt: $command->startsAt ?? throw new InvalidLiveTrainingDetailsException('La date de début est requise pour un LIVE.'),
                endsAt: $command->endsAt ?? throw new InvalidLiveTrainingDetailsException('La date de fin est requise pour un LIVE.'),
                deliveryMode: $command->deliveryMode,
                location: self::clean($command->location),
                joinUrl: self::clean($command->joinUrl),
                liveSource: $liveSource,
                replaySource: $replaySource,
            );
            if ($training->liveDetails !== null) {
                $details->update($command->startsAt ?? throw new InvalidLiveTrainingDetailsException('La date de début est requise pour un LIVE.'), $command->endsAt ?? throw new InvalidLiveTrainingDetailsException('La date de fin est requise pour un LIVE.'), $command->deliveryMode, self::clean($command->location), self::clean($command->joinUrl), $liveSource, $replaySource);
            }
            $training->replaceLiveDetails($details);
        }

        $oldCoverId = $training->coverMediaId;
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'training/covers') : null;
            if ($media !== null) {
                $training->setCoverMedia($media->id);
            } elseif ($command->removeCover) {
                $training->setCoverMedia(null);
            }
            $this->repository->save($training);

            if ($oldCoverId !== null && ($media !== null || $command->removeCover)) {
                $this->removeIfOrphaned($oldCoverId);
            }
        } catch (\Throwable $exception) {
            if ($media !== null) {
                try { $this->mediaUpload->delete($media); } catch (\Throwable) {}
            }
            throw $exception;
        }
    }

    private function removeIfOrphaned(int $mediaId): void
    {
        try {
            $this->mediaUpload->delete($this->mediaRepository->getById($mediaId));
        } catch (MediaInUseException) {
        }
    }

    private static function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;
        return $value === '' ? null : $value;
    }

}
