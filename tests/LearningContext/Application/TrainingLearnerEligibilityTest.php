<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryUser;
use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibility;

final class TrainingLearnerEligibilityTest extends TestCase
{
    public function testActiveAvocatIsEligible(): void
    {
        $users = $this->createMock(UserDirectoryInterface::class);
        $users->method('getById')->willReturn(new UserDirectoryUser(1, 'uuid', 'Avocat', 'avocat@example.test', true, ['ROLE_AVOCAT']));

        self::assertTrue((new TrainingLearnerEligibility($users))->isEligible(1));
    }

    /** @dataProvider ineligibleUsers */
    public function testInactiveOrNonAvocatIsNotEligible(UserDirectoryUser $user): void
    {
        $users = $this->createMock(UserDirectoryInterface::class);
        $users->method('getById')->willReturn($user);

        self::assertFalse((new TrainingLearnerEligibility($users))->isEligible($user->id));
    }

    /** @return iterable<string, array{UserDirectoryUser}> */
    public static function ineligibleUsers(): iterable
    {
        yield 'regular user' => [new UserDirectoryUser(2, 'uuid', 'User', 'user@example.test', true, ['ROLE_USER'])];
        yield 'admin without explicit avocat role' => [new UserDirectoryUser(3, 'uuid', 'Admin', 'admin@example.test', true, ['ROLE_ADMIN'])];
        yield 'disabled avocat' => [new UserDirectoryUser(4, 'uuid', 'Disabled avocat', 'disabled@example.test', false, ['ROLE_AVOCAT'])];
    }
}
