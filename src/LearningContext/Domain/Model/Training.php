<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\InvalidTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Exception\InvalidTrainingTransitionException;

final class Training
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly TrainingType $type,
        public string $title,
        public string $slug,
        public string $summary,
        public string $description,
        public TrainingVisibility $visibility,
        public TrainingAccessType $accessType,
        public TrainingStatus $status = TrainingStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?int $coverMediaId = null,
        /** @var list<int> */
        public array $categoryIds = [],
        /** @var list<int> */
        public array $tagIds = [],
        public ?LiveTrainingDetails $liveDetails = null,
    ) {
        if ($this->liveDetails !== null && $this->type !== TrainingType::LIVE) {
            throw new InvalidTrainingDetailsException('Les détails LIVE sont réservés aux formations de type LIVE.');
        }
    }

    public function update(
        string $title,
        string $slug,
        string $summary,
        string $description,
        TrainingVisibility $visibility,
        TrainingAccessType $accessType,
    ): void {
        $this->title = $title;
        $this->summary = $summary;
        $this->description = $description;
        $this->visibility = $visibility;
        $this->accessType = $accessType;
        if ($this->status === TrainingStatus::DRAFT) {
            $this->slug = $slug;
        }
    }

    public function setCoverMedia(?int $mediaId): void
    {
        $this->coverMediaId = $mediaId;
    }

    /**
     * @param list<int> $categoryIds
     * @param list<int> $tagIds
     */
    public function replaceClassification(array $categoryIds, array $tagIds): void
    {
        $this->categoryIds = array_values(array_unique($categoryIds));
        $this->tagIds = array_values(array_unique($tagIds));
    }

    public function replaceLiveDetails(?LiveTrainingDetails $details): void
    {
        if ($details !== null && $this->type !== TrainingType::LIVE) {
            throw new InvalidTrainingDetailsException('Les détails LIVE sont réservés aux formations de type LIVE.');
        }
        $this->liveDetails = $details;
    }

    public function publish(int $moduleCount = 0, int $emptyModuleCount = 0, int $unreadyLessonCount = 0): void
    {
        if ($this->status !== TrainingStatus::DRAFT) {
            throw new InvalidTrainingTransitionException('Seul un brouillon peut être publié.');
        }
        $this->assertPublishable();
        if ($this->type === TrainingType::COURSE && ($moduleCount < 1 || $emptyModuleCount > 0)) {
            throw new InvalidTrainingDetailsException('Ajoutez au moins un module contenant une leçon avant de publier cette formation.');
        }
        if ($this->type === TrainingType::COURSE && $unreadyLessonCount > 0) {
            throw new InvalidTrainingDetailsException('Chaque leçon doit contenir un contenu, une vidéo YouTube valide ou au moins une ressource avant la publication.');
        }
        if ($this->type === TrainingType::LIVE) {
            if ($this->liveDetails === null) {
                throw new InvalidTrainingDetailsException('Une formation LIVE doit définir sa session avant sa publication.');
            }
            $this->liveDetails->assertValid();
        }
        $this->status = TrainingStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function archive(): void
    {
        if ($this->status !== TrainingStatus::PUBLISHED) {
            throw new InvalidTrainingTransitionException('Seule une formation publiée peut être archivée.');
        }
        $this->status = TrainingStatus::ARCHIVED;
    }

    private function assertPublishable(): void
    {
        if (trim($this->title) === '') {
            throw new InvalidTrainingDetailsException('Une formation doit avoir un titre pour être publiée.');
        }
        if (trim($this->summary) === '') {
            throw new InvalidTrainingDetailsException('Une formation doit avoir un résumé pour être publiée.');
        }
        if (trim(strip_tags($this->description)) === '') {
            throw new InvalidTrainingDetailsException('Une formation doit avoir une description pour être publiée.');
        }
    }
}
