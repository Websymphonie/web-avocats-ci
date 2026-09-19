<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventDetailsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventTransitionException;
use Websymphonie\ContentContext\Domain\Model\Event;

final class EventTest extends TestCase
{
    public function testNewEventStartsAsDraft(): void
    {
        $event = $this->event();
        self::assertSame(EventStatus::DRAFT, $event->status);
        self::assertNull($event->publishedAt);
    }

    public function testEndCannotBeBeforeStart(): void
    {
        $this->expectException(InvalidEventDetailsException::class);
        $this->event(new DateTimeImmutable('2026-09-20 10:00'), new DateTimeImmutable('2026-09-20 09:00'));
    }

    public function testOnlineEventRequiresSafeParticipationUrl(): void
    {
        $this->expectException(InvalidEventDetailsException::class);
        new Event(0, '', 'Titre', 'titre', null, '<p>Description</p>', EventFormat::ONLINE, new DateTimeImmutable('2026-09-20 10:00'), null, null, null, 'javascript:alert(1)');
    }

    public function testPublishCancelAndArchiveFollowTheLifecycle(): void
    {
        $event = $this->event();
        $event->publish();
        self::assertSame(EventStatus::PUBLISHED, $event->status);
        self::assertNotNull($event->publishedAt);
        $event->cancel();
        self::assertSame(EventStatus::CANCELLED, $event->status);
        $event->archive();
        self::assertSame(EventStatus::ARCHIVED, $event->status);
    }

    public function testInvalidTransitionIsRejected(): void
    {
        $event = $this->event();
        $this->expectException(InvalidEventTransitionException::class);
        $event->cancel();
    }

    private function event(?DateTimeImmutable $startsAt = null, ?DateTimeImmutable $endsAt = null): Event
    {
        return new Event(0, '', 'Titre', 'titre', null, '<p>Description</p>', EventFormat::IN_PERSON, $startsAt ?? new DateTimeImmutable('2026-09-20 10:00'), $endsAt ?? new DateTimeImmutable('2026-09-20 12:00'), 'Maison de l’Avocat', 'Abidjan, Côte d’Ivoire', null);
    }
}
