<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class LawyerPublicProfile
{
    public function __construct(
        public string $publicUuid,
        public string $name,
        public ?string $barNumber,
        public ?string $specializationSummary,
        public ?string $biography,
        public ?string $professionalPhone,
        public ?string $professionalEmail,
        public ?int $portraitMediaId,
        public ?string $cabinetName,
        public ?string $publicCabinetUuid,
        public ?string $publicCabinetCity,
    ) {
    }
}
