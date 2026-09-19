<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Security;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Revokes every persistent Remember Me token for a login identifier.
 */
final readonly class RememberMeTokenRevoker
{
    public function __construct(private Connection $connection)
    {
    }

    public function revokeAllForUserIdentifier(string $identifier): void
    {
        $this->connection->executeStatement(
            'DELETE FROM rememberme_token WHERE username = :username',
            ['username' => $identifier],
            ['username' => ParameterType::STRING],
        );
    }
}
