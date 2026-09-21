<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetPublicTrainingBySlugQuery
{
    public function __construct(public string $slug)
    {
    }
}
