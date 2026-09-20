<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class DbLogListener
{
    private static int $disabledDepth = 0;

    public function __construct(
        private readonly LoggerInterface $dbLogger,
        private readonly SensitiveLogDataSanitizer $sanitizer,
    )
    {
    }

    public static function disable(): void
    {
        ++self::$disabledDepth;
    }

    public static function enable(): void
    {
        self::$disabledDepth = max(0, self::$disabledDepth - 1);
    }

    public static function withoutLogging(callable $operation): mixed
    {
        self::disable();

        try {
            return $operation();
        } finally {
            self::enable();
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        if (self::$disabledDepth > 0) return;
        $entity = $args->getObject();
        if ($entity instanceof Logs || $entity instanceof Notifications || $entity instanceof AuthLog) {
            return;
        }
        $entityClass = (new ReflectionClass($entity))->getShortName();
        $this->dbLogger->info("$entityClass créé(e)");
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        if (self::$disabledDepth > 0) return;
        $entity = $args->getObject();
        if ($entity instanceof Logs || $entity instanceof Notifications || $entity instanceof AuthLog) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $uow = $entityManager->getUnitOfWork();
        $changes = $uow->getEntityChangeSet($entity);

        if (!empty($changes)) {
            foreach ($changes as $field => $values) {
                if ($field === 'updatedAt') {
                    continue; // 🔥 On ignore updatedAt
                }
                if ($values instanceof PersistentCollection) {
                    continue;
                }
                [$oldValue, $newValue] = $values;
                $change = $this->sanitizer->isSensitiveKey($field)
                    ? sprintf('Champ "%s" modifié : %s', $field, SensitiveLogDataSanitizer::REDACTED)
                    : sprintf(
                        'Champ "%s" : "%s" => "%s"',
                        $field,
                        $this->valueToString($oldValue, $field),
                        $this->valueToString($newValue, $field),
                    );

                $this->dbLogger->info(sprintf(
                    'Donnée modifiée (%s) - %s',
                    (new ReflectionClass($entity))->getShortName(),
                    $change,
                ));
            }
        } else {
            $this->dbLogger->info('Entité modifiée sans changement détecté: ' . (new ReflectionClass($entity))->getShortName());
        }
    }

    private function valueToString(mixed $value, ?string $field = null): string
    {
        return $this->sanitizer->stringify($value, $field);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        if (self::$disabledDepth > 0) return;
        $entity = $args->getObject();
        if ($entity instanceof Logs || $entity instanceof Notifications || $entity instanceof AuthLog) {
            return;
        }
        $entityClass = (new ReflectionClass($entity))->getShortName();
        $this->dbLogger->info("$entityClass supprimé(e)");
    }

}
