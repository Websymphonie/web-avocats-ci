<?php
declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Security;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;

final class RememberMeTokenRevokerTest extends TestCase
{
    public function testRevokesAllTokensForIdentifier(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'DELETE FROM rememberme_token WHERE username = :username',
                ['username' => 'user@example.test'],
                self::anything(),
            )
            ->willReturn(2);

        (new RememberMeTokenRevoker($connection))->revokeAllForUserIdentifier('user@example.test');
    }
}
