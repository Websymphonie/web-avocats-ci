<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final readonly class DeleteNewsCommand
{
    public function __construct(public int $id) {}
}
