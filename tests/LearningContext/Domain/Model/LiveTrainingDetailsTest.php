<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;

final class LiveTrainingDetailsTest extends TestCase
{
    public function testOnlineModeRequiresHttpsAndCanBeValid(): void
    {
        $details = $this->details(LiveDeliveryMode::ONLINE, null, 'https://meet.example.test/live');

        self::assertTrue($details->hasJoinUrl());
    }

    public function testInPersonModeRequiresLocation(): void
    {
        $this->expectException(InvalidLiveTrainingDetailsException::class);

        $this->details(LiveDeliveryMode::IN_PERSON);
    }

    public function testHybridModeRequiresLocationAndHttpsUrl(): void
    {
        $this->expectException(InvalidLiveTrainingDetailsException::class);

        $this->details(LiveDeliveryMode::HYBRID, 'Maison de l’Avocat', 'http://meet.example.test/live');
    }

    public function testDatesMustBeStrictlyOrdered(): void
    {
        $this->expectException(InvalidLiveTrainingDetailsException::class);

        new LiveTrainingDetails(0, '', 10, new DateTimeImmutable('2026-10-01 10:00:00'), new DateTimeImmutable('2026-10-01 10:00:00'), LiveDeliveryMode::ONLINE, joinUrl: 'https://meet.example.test/live');
    }

    private function details(LiveDeliveryMode $mode, ?string $location = null, ?string $joinUrl = 'https://meet.example.test/live'): LiveTrainingDetails
    {
        return new LiveTrainingDetails(0, '', 10, new DateTimeImmutable('2026-10-01 10:00:00'), new DateTimeImmutable('2026-10-01 11:00:00'), $mode, $location, $joinUrl);
    }
}
