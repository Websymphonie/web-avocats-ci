<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;

final class CreatePageCommand
{
    public function __construct(
        public string $title = '',
        public string $slug = '',
        public string $content = '',
        public ?UploadedFile $cover = null,
        public ?PageGroup $group = null,
    ) {
    }
}
