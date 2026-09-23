<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Import;

final readonly class DownloadedLegacyPortrait
{
    public function __construct(public string $contents, public string $mimeType, public string $extension)
    {
    }
}
