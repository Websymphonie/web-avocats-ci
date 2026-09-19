<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Event;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;

final class UpdateEventCommand
{
    /**
     * @param list<int> $categories
     * @param list<int> $tags
     */
    public function __construct(
        public int $id, public string $title = '', public ?string $excerpt = null, public string $description = '',
        public EventFormat $format = EventFormat::IN_PERSON, public ?DateTimeImmutable $startsAt = null, public ?DateTimeImmutable $endsAt = null,
        public ?string $venueName = null, public ?string $address = null, public ?string $onlineUrl = null,
        public array $categories = [], public array $tags = [],
    ) {}
}
