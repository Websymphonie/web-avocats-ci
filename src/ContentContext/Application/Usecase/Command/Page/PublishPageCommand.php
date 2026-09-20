<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

final readonly class PublishPageCommand
{
    public function __construct(public int $id) {}
}
