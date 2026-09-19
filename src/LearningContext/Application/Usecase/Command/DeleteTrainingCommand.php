<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

final readonly class DeleteTrainingCommand
{
    public function __construct(public int $id) {}
}
