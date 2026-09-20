<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

final class UpdatePageCommand
{
    public function __construct(
        public readonly int $id,
        public string $title = '',
        public string $slug = '',
        public string $content = '',
    ) {
    }
}
