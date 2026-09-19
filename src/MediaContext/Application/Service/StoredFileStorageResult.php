<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

final readonly class StoredFileStorageResult
{
    public function __construct(
        public string $originalName,
        public string $storageName,
        public string $mimeType,
        public int $size,
        public string $checksum,
    ) {}
}
