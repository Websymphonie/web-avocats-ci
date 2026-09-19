<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Factory\Log;

use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\LogContext\Domain\Model\Log\LogModel;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;

final readonly class LogFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    /**
     * @param list<Logs> $entities
     * @return list<LogModel>
     */
    public function fromEntityList(array $entities): array
    {
        return array_map(fn(Logs $log) => self::fromEntity($log), $entities);
    }

    public function fromEntity(?Logs $log): ?LogModel
    {
        if ($log === null) {
            return null;
        }
        return new LogModel(
            id: $log->getId(),
            uuid: $log->getUuidAsString(),
            message: $log->getMessage(),
            context: $log->getContext(),
            level: $log->getLevel(),
            levelName: $log->getLevelName(),
            extra: $log->getExtra(),
            user: $log->getUser() !== null ? $this->userFactory->fromEntity($log->getUser()) : null,
            createdAt: $log->getCreatedAt(),
            updatedAt: $log->getUpdatedAt(),
        );
    }
}
