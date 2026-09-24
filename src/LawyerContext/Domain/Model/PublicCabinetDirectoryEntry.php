<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class PublicCabinetDirectoryEntry
{
    public function __construct(
        public string $publicUuid,
        public string $name,
    ) {
    }
}
