<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetMemberTrainingSummariesQuery
{
    public function __construct(public int $userId)
    {
    }
}
