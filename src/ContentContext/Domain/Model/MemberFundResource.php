<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;

final readonly class MemberFundResource
{
    public function __construct(
        public string $uuid,
        public string $title,
        public string $description,
        public ?DateTimeImmutable $publishedAt,
    ) {}
}
