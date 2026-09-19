<?php
declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Persistence\Doctrine\Entity;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class UserSecurityTest extends TestCase
{
    public function testEquivalentUsersIgnoreRoleOrdering(): void
    {
        $first = $this->user(['ROLE_USER', 'ROLE_ADMIN']);
        $second = clone $first;
        $second->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        self::assertTrue($first->isEqualTo($second));
    }

    public function testSecurityVersionMismatchInvalidatesContext(): void
    {
        $serialized = unserialize(serialize($this->user()));
        self::assertInstanceOf(User::class, $serialized);
        $fresh = clone $serialized;
        $fresh->incrementSecurityVersion();

        self::assertFalse($serialized->isEqualTo($fresh));
    }

    /** @param list<string> $roles */
    private function user(array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail('security@example.test');
        $user->setPassword('hash');
        $user->setEnabled(true);
        $user->setRoles($roles);

        return $user;
    }
}
