<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

final readonly class DeletePageCommand
{
    public function __construct(public int $id) {}
}
