<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;

final class UpdateTrainingCommand
{
    public function __construct(
        public int $id,
        public string $title = '',
        public string $summary = '',
        public string $description = '',
        public TrainingVisibility $visibility = TrainingVisibility::PUBLIC,
        public TrainingAccessType $accessType = TrainingAccessType::FREE,
        public readonly TrainingType $type = TrainingType::COURSE,
        public ?UploadedFile $cover = null,
        public bool $removeCover = false,
        /** @var list<int> */
        public array $categoryIds = [],
        /** @var list<int> */
        public array $tagIds = [],
        public ?DateTimeImmutable $startsAt = null,
        public ?DateTimeImmutable $endsAt = null,
        public LiveDeliveryMode $deliveryMode = LiveDeliveryMode::ONLINE,
        public ?string $location = null,
        public ?string $joinUrl = null,
        public ?string $youtubeStreamUrl = null,
    ) {}
}
