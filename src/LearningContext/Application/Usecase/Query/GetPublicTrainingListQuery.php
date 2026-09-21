<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;

final readonly class GetPublicTrainingListQuery
{
    public function __construct(
        public ?TrainingType $type = null,
        public ?TrainingAccessType $accessType = null,
        public ?int $categoryId = null,
        public int $page = 1,
        public int $limit = 15,
    ) {
    }
}
