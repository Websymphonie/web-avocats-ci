<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LawyerContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\LawyerContext\Application\Service\LawyerDirectoryPublicationPolicy;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;

final class LawyerDirectoryPublicationPolicyTest extends TestCase
{
    public function testPublicationDefaultsArePrivate(): void
    {
        self::assertFalse((new LawyerProfileEntity())->isDirectoryVisible());
        self::assertFalse((new CabinetEntity())->isDirectoryVisible());
    }

    public function testPublicEnabledAvocatWithAllowedProfessionalStatusIsEligible(): void
    {
        self::assertTrue((new LawyerDirectoryPublicationPolicy())->isLawyerEligible(true, true, ['ROLE_AVOCAT'], 'TRAINEE'));
    }

    public function testAccountlessVisibleProfilesCanBePublishedWithUnknownOrKnownStatus(): void
    {
        $policy = new LawyerDirectoryPublicationPolicy();

        self::assertTrue($policy->isLawyerEligible(true, null, [], 'UNKNOWN'));
        self::assertTrue($policy->isLawyerEligible(true, null, [], 'ACTIVE'));
        self::assertFalse($policy->isLawyerEligible(true, null, [], 'SUSPENDED'));
        self::assertFalse($policy->isLawyerEligible(false, null, [], 'UNKNOWN'));
    }

    public function testLawyerIsNotEligibleWhenAnyPublicationCriterionFails(): void
    {
        $policy = new LawyerDirectoryPublicationPolicy();
        $criteria = [
            [false, true, ['ROLE_AVOCAT'], 'ACTIVE'],
            [true, false, ['ROLE_AVOCAT'], 'ACTIVE'],
            [true, true, ['ROLE_USER'], 'ACTIVE'],
            [true, true, ['ROLE_AVOCAT'], 'SUSPENDED'],
        ];

        foreach ($criteria as [$visible, $enabled, $roles, $status]) {
            self::assertFalse($policy->isLawyerEligible($visible, $enabled, $roles, $status));
        }
    }

    public function testCabinetPublicationRequiresVisibleAndActive(): void
    {
        $policy = new LawyerDirectoryPublicationPolicy();

        self::assertTrue($policy->isCabinetEligible('ACTIVE', true));
        self::assertFalse($policy->isCabinetEligible('ACTIVE', false));
        self::assertFalse($policy->isCabinetEligible('INACTIVE', true));
        self::assertFalse($policy->isCabinetEligible('ARCHIVED', true));
    }
}
