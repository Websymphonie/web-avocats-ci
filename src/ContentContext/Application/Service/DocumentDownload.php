<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

final readonly class DocumentDownload
{
    public function __construct(public string $path, public string $originalName, public string $mimeType) {}
}
