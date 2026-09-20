<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

final class CreatePageCommand
{
    public function __construct(
        public string $title = '',
        public string $slug = '',
        public string $content = '',
    ) {
    }
}
