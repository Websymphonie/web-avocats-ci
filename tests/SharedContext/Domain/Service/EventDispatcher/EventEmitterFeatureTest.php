<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Domain\Service\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventEmitterFeature;

final class EventEmitterFeatureTest extends TestCase
{
    public function testReleaseReturnsEventsInOrderAndClearsTheBuffer(): void
    {
        $emitter = new class {
            use EventEmitterFeature;
        };
        $first = new \stdClass();
        $second = new \stdClass();
        $emitter->emitEvent($first);
        $emitter->emitEvent($second);

        self::assertSame([$first, $second], $emitter->releaseEvents());
        self::assertSame([], $emitter->releaseEvents());
    }

    public function testEventsEmittedAfterReleaseAreAvailableInTheNextRelease(): void
    {
        $emitter = new class {
            use EventEmitterFeature;
        };
        $first = new \stdClass();
        $second = new \stdClass();
        $emitter->emitEvent($first);
        $emitter->releaseEvents();
        $emitter->emitEvent($second);

        self::assertSame([$second], $emitter->releaseEvents());
    }
}
