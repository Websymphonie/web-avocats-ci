<?php
declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation\AccountActivation;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class AccountActivationTest extends TestCase
{
    public function testTokenIsStoredAsHashAndIsSingleUse(): void
    {
        $now = new DateTimeImmutable('2026-09-11 10:00:00');
        $raw = 'a-secure-random-secret';
        $activation = new AccountActivation(new User(), 'selector', hash('sha256', $raw), $now->modify('+24 hours'), $now);

        self::assertNotSame($raw, hash('sha256', $raw));
        self::assertTrue($activation->matchesToken($raw));
        self::assertFalse($activation->matchesToken('wrong-token'));
        self::assertTrue($activation->isUsable($now));

        $activation->consume($now);
        self::assertFalse($activation->isUsable($now));
    }

    public function testExpiredTokenIsNotUsable(): void
    {
        $now = new DateTimeImmutable('2026-09-11 10:00:00');
        $activation = new AccountActivation(new User(), 'selector', hash('sha256', 'secret'), $now->modify('-1 second'), $now->modify('-24 hours'));
        self::assertFalse($activation->isUsable($now));
    }
}
