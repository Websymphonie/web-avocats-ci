<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;

final class CreateTrainingCommand
{
    public function __construct(
        public string $title = '',
        public string $summary = '',
        public string $description = '',
        public TrainingVisibility $visibility = TrainingVisibility::PUBLIC,
        public TrainingAccessType $accessType = TrainingAccessType::FREE,
        public ?UploadedFile $cover = null,
        /** @var list<int> */
        public array $categoryIds = [],
        /** @var list<int> */
        public array $tagIds = [],
    ) {}
}
