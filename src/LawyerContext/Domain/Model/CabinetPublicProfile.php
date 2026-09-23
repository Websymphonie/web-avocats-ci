<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class CabinetPublicProfile
{
    /**
     * @param list<string> $phones
     * @param list<LawyerDirectoryMember> $members
     */
    public function __construct(
        public string $publicUuid,
        public string $name,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public array $phones,
        public ?string $email,
        public ?string $websiteUrl,
        public ?string $description,
        public array $members = [],
    ) {
    }
}
