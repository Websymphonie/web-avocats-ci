<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Factory\AuthLog;

use Websymphonie\LogContext\Domain\Model\AuthLog\AuthLogModel;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;

final class AuthLogFactory
{
    /**
     * @param list<AuthLog> $entities
     * @return list<AuthLogModel>
     */
    public function fromEntityList(array $entities): array
    {
        return array_map(fn(AuthLog $log) => self::fromEntity($log), $entities);
    }

    public function fromEntity(?AuthLog $log): ?AuthLogModel
    {
        if ($log === null) {
            return null;
        }
        return new AuthLogModel(
            id: $log->getId(),
            uuid: $log->getUuidAsString(),
            userIp: $log->getUserIP(),
            emailEntered: $log->getEmailEntered(),
            isSuccessFulAuth: $log->getIsSuccessFulAuth(),
            startOfBlackListing: $log->getStartOfBlackListing(),
            endOfBlackListing: $log->getEndOfBlackListing(),
            isRememberMeAuth: $log->getIsRememberMeAuth(),
            deauthenticatedAt: $log->getDeauthenticatedAt(),
            authAttemptAt: $log->getAuthAttemptAt(),
            createdAt: $log->getCreatedAt(),
            updatedAt: $log->getUpdatedAt(),
        );
    }
}
