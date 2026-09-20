<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Domain\Service\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventEmitterFeature;

final class EventEmitterFeatureTest extends TestCase
{
    public function testItStoresEventsInEmissionOrder(): void
    {
        $emitter = new class {
            use EventEmitterFeature;
        };
        $first = new \stdClass();
        $second = new \stdClass();

        $emitter->emitEvent($first);
        $emitter->emitEvent($second);

        self::assertSame([$first, $second], $emitter->releaseEvents());
    }

    public function testReleaseDoesNotClearTheStoredEvents(): void
    {
        $emitter = new class {
            use EventEmitterFeature;
        };
        $event = new \stdClass();
        $emitter->emitEvent($event);

        self::assertSame([$event], $emitter->releaseEvents());
        self::assertSame([$event], $emitter->releaseEvents());
    }
}
