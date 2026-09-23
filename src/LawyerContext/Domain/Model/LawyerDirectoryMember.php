<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class LawyerDirectoryMember
{
    public function __construct(
        public string $publicUuid,
        public string $name,
        public ?int $portraitMediaId,
    ) {
    }
}
