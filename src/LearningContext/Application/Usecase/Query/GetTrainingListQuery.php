<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;

final class GetTrainingListQuery
{
    public function __construct(
        public ?string $search = null,
        public ?TrainingStatus $status = null,
        public ?TrainingVisibility $visibility = null,
        public ?TrainingAccessType $accessType = null,
        public ?TrainingType $type = null,
        public ?int $categoryId = null,
        public ?int $tagId = null,
        public int $page = 1,
        public int $limit = 20,
    ) {}
}
