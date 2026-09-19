<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Model;

use DateTimeImmutable;

/** A public image asset. It deliberately has no relation to its business consumers. */
final readonly class Media
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $originalName,
        public string $storageName,
        public string $mimeType,
        public int $size,
        public int $width,
        public int $height,
        public string $storagePath,
        public ?DateTimeImmutable $createdAt = null,
    ) {
    }
}
