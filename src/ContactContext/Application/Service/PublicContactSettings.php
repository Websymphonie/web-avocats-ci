<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

final readonly class PublicContactSettings
{
    public function __construct(
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $hours = null,
    ) {
    }
}
