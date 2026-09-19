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
        public TrainingType $type,
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
    ) {
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

    public function publish(): void
    {
        if ($this->status !== TrainingStatus::DRAFT) {
            throw new InvalidTrainingTransitionException('Seul un brouillon peut être publié.');
        }
        $this->assertPublishable();
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
