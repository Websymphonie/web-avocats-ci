<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Model;

use DateTimeImmutable;

final class StoredFile
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $originalName,
        public string $storageName,
        public string $mimeType,
        public int $size,
        public string $checksum,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
