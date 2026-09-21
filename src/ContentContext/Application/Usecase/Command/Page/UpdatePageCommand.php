<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;

final class UpdatePageCommand
{
    public function __construct(
        public readonly int $id,
        public string $title = '',
        public string $slug = '',
        public string $content = '',
        public ?UploadedFile $cover = null,
        public bool $removeCover = false,
        public ?PageGroup $group = null,
        public int $sortOrder = 0,
    ) {
    }
}
