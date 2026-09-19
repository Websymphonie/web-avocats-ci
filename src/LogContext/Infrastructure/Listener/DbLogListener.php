<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Listener;

use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class DbLogListener
{
    private static bool $enabled = true;

    public function __construct(private readonly LoggerInterface $dbLogger)
    {
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        if (!self::$enabled) return;
        $entity = $args->getObject();
        if ($entity instanceof Logs || $entity instanceof Notifications || $entity instanceof AuthLog) {
            return;
        }
        $entityClass = (new ReflectionClass($entity))->getShortName();
        $this->dbLogger->info("$entityClass créé(e)");
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        if (!self::$enabled) return;
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
                $this->dbLogger->info(sprintf(
                    'Donnée modifiée (%s) - Champ "%s" : "%s" => "%s"',
                    (new ReflectionClass($entity))->getShortName(),
                    $field,
                    $this->valueToString($oldValue),
                    $this->valueToString($newValue)
                ));
            }
        } else {
            $this->dbLogger->info('Entité modifiée sans changement détecté: ' . (new ReflectionClass($entity))->getShortName());
        }
    }

    private function valueToString(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string)$value : '[object]';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string)$value;
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        if (!self::$enabled) return;
        $entity = $args->getObject();
        if ($entity instanceof Logs || $entity instanceof Notifications || $entity instanceof AuthLog) {
            return;
        }
        $entityClass = (new ReflectionClass($entity))->getShortName();
        $this->dbLogger->info("$entityClass supprimé(e)");
    }

}
