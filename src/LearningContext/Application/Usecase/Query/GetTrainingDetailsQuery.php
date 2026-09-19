<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetTrainingDetailsQuery
{
    public function __construct(public int $id) {}
}
