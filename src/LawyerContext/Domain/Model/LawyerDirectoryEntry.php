<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class LawyerDirectoryEntry
{
    public function __construct(
        public string $publicUuid,
        public string $name,
        public ?string $cabinetName,
        public ?string $location,
        public ?int $portraitMediaId,
    ) {
    }
}
