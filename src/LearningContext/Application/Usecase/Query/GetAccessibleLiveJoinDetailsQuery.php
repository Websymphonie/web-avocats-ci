<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetAccessibleLiveJoinDetailsQuery
{
    public function __construct(public string $trainingUuid, public int $userId) {}
}
