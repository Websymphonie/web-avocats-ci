<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\Command;

final readonly class DeleteMediaCommand
{
    public function __construct(public int $id) {}
}
