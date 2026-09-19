<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;

final readonly class GetTrainingEnrollmentsQuery
{
    public function __construct(public int $trainingId, public ?EnrollmentStatus $status = null, public ?EnrollmentSource $source = null, public ?string $search = null, public int $page = 1, public int $limit = 25) {}
}
