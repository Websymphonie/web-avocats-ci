<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventDetailsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventTransitionException;

final class Event
{
    /**
     * @param list<EventCategory> $categories
     * @param list<Tag> $tags
     */
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $description,
        public EventFormat $format,
        public DateTimeImmutable $startsAt,
        public ?DateTimeImmutable $endsAt,
        public ?string $venueName,
        public ?string $address,
        public ?string $onlineUrl,
        public EventStatus $status = EventStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public array $categories = [],
        public array $tags = [],
    ) {
        self::assertDetails($format, $startsAt, $endsAt, $venueName, $address, $onlineUrl);
    }

    public function update(
        string $title,
        string $slug,
        ?string $excerpt,
        string $description,
        EventFormat $format,
        DateTimeImmutable $startsAt,
        ?DateTimeImmutable $endsAt,
        ?string $venueName,
        ?string $address,
        ?string $onlineUrl,
    ): void {
        self::assertDetails($format, $startsAt, $endsAt, $venueName, $address, $onlineUrl);
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->description = $description;
        $this->format = $format;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->venueName = $venueName;
        $this->address = $address;
        $this->onlineUrl = $onlineUrl;
        if ($this->status === EventStatus::DRAFT) {
            $this->slug = $slug;
        }
    }

    /** @param list<EventCategory> $categories */
    public function replaceCategories(array $categories): void { $this->categories = $categories; }

    /** @param list<Tag> $tags */
    public function replaceTags(array $tags): void { $this->tags = $tags; }

    public function publish(): void
    {
        if ($this->status !== EventStatus::DRAFT) {
            throw new InvalidEventTransitionException('Seul un brouillon peut être publié.');
        }
        if (trim($this->title) === '' || trim(strip_tags($this->description)) === '') {
            throw new InvalidEventDetailsException('Un événement doit avoir un titre et une description pour être publié.');
        }
        self::assertDetails($this->format, $this->startsAt, $this->endsAt, $this->venueName, $this->address, $this->onlineUrl);
        $this->status = EventStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status !== EventStatus::PUBLISHED) {
            throw new InvalidEventTransitionException('Seul un événement publié peut être annulé.');
        }
        $this->status = EventStatus::CANCELLED;
    }

    public function archive(): void
    {
        if (!in_array($this->status, [EventStatus::PUBLISHED, EventStatus::CANCELLED], true)) {
            throw new InvalidEventTransitionException('Seul un événement publié ou annulé peut être archivé.');
        }
        $this->status = EventStatus::ARCHIVED;
    }

    private static function assertDetails(EventFormat $format, DateTimeImmutable $startsAt, ?DateTimeImmutable $endsAt, ?string $venueName, ?string $address, ?string $onlineUrl): void
    {
        if ($endsAt !== null && $endsAt < $startsAt) {
            throw new InvalidEventDetailsException('La date de fin doit être postérieure ou égale à la date de début.');
        }
        $physical = trim((string) $venueName) !== '' && trim((string) $address) !== '';
        $online = self::isSafeUrl($onlineUrl);
        if (in_array($format, [EventFormat::IN_PERSON, EventFormat::HYBRID], true) && !$physical) {
            throw new InvalidEventDetailsException('Le nom du lieu et l’adresse sont requis pour ce format.');
        }
        if (in_array($format, [EventFormat::ONLINE, EventFormat::HYBRID], true) && !$online) {
            throw new InvalidEventDetailsException('Une URL de participation valide est requise pour ce format.');
        }
    }

    private static function isSafeUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }
}
