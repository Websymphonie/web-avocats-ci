<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Exception\InvalidCouncilMemberException;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;

final class CouncilMemberTest extends TestCase
{
    public function testMemberIsCurrentWhenNoEndDateExists(): void
    {
        $member = new CouncilMember(1, 'uuid', 'Me Awa Kouassi', 'Secrétaire', null);

        self::assertTrue($member->isCurrent());
    }

    public function testHistoricalMemberIsNotCurrent(): void
    {
        $member = new CouncilMember(1, 'uuid', 'Me Awa Kouassi', 'Secrétaire', null, mandateEndedAt: new DateTimeImmutable('2024-01-01'));

        self::assertFalse($member->isCurrent());
    }

    public function testRequiredValuesAndDatesAreValidated(): void
    {
        $this->expectException(InvalidCouncilMemberException::class);
        new CouncilMember(1, 'uuid', '', 'Secrétaire', null);
    }

    public function testEndDateCannotPrecedeStartDate(): void
    {
        $this->expectException(InvalidCouncilMemberException::class);
        new CouncilMember(1, 'uuid', 'Me Awa Kouassi', 'Secrétaire', null, mandateStartedAt: new DateTimeImmutable('2025-01-01'), mandateEndedAt: new DateTimeImmutable('2024-01-01'));
    }

    public function testUpdateKeepsTheDomainInvariants(): void
    {
        $member = new CouncilMember(1, 'uuid', 'Ancien nom', 'Membre', null);
        $member->update('Nouveau nom', 'Présidente', 2, null, null);

        self::assertSame('Nouveau nom', $member->fullName);
        self::assertSame('Présidente', $member->function);
        self::assertSame(2, $member->sortOrder);
    }
}
