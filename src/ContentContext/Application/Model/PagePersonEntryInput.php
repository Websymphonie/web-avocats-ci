<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PagePersonEntryInput
{
    public string $key = '';
    public string $displayName = '';
    public ?string $roleLabel = null;
    public ?string $periodLabel = null;
    public ?int $portraitMediaId = null;
    public ?UploadedFile $portrait = null;
    public bool $removePortrait = false;
    public ?string $linkUrl = null;
    public int $sortOrder = 0;
}
