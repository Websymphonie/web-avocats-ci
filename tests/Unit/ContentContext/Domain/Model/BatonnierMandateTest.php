<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Exception\InvalidBatonnierMandateException;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;

final class BatonnierMandateTest extends TestCase
{
    public function testNewMandateIsCurrentAndNormalizesSummary(): void
    {
        $mandate = new BatonnierMandate(1, 'uuid', '  Me Jane Doe  ', null, new DateTimeImmutable('2026-01-01'), null, ' <b>Présentation</b> ');

        self::assertSame('Me Jane Doe', $mandate->fullName);
        self::assertTrue($mandate->isCurrent());
        self::assertSame('Présentation', $mandate->summary);
    }

    public function testEndedMandateIsNotCurrent(): void
    {
        $mandate = new BatonnierMandate(1, 'uuid', 'Me Jane Doe', null, new DateTimeImmutable('2020-01-01'), new DateTimeImmutable('2024-01-01'), null);

        self::assertFalse($mandate->isCurrent());
    }

    public function testEndCannotPrecedeStart(): void
    {
        $this->expectException(InvalidBatonnierMandateException::class);
        new BatonnierMandate(1, 'uuid', 'Me Jane Doe', null, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2025-01-01'), null);
    }

    public function testNameIsRequired(): void
    {
        $this->expectException(InvalidBatonnierMandateException::class);
        new BatonnierMandate(1, 'uuid', ' ', null, new DateTimeImmutable('2026-01-01'), null, null);
    }

    public function testUpdatePreservesMutableMandateRules(): void
    {
        $mandate = new BatonnierMandate(1, 'uuid', 'Me Jane Doe', null, new DateTimeImmutable('2020-01-01'), null, null);
        $mandate->update('Me John Doe', new DateTimeImmutable('2021-01-01'), new DateTimeImmutable('2025-01-01'), 'Présentation');

        self::assertSame('Me John Doe', $mandate->fullName);
        self::assertFalse($mandate->isCurrent());
    }
}
