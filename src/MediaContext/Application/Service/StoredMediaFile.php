<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

final readonly class StoredMediaFile
{
    public function __construct(
        public string $originalName,
        public string $storageName,
        public string $mimeType,
        public int $size,
        public int $width,
        public int $height,
        public string $storagePath,
    ) {
    }
}
