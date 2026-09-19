<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final readonly class AuditableListener
{
    public function __construct(private Security $security)
    {
    }

    /** @param LifecycleEventArgs<ObjectManager> $args */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof AuditableInterface) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user) {
            $entity->setCreatedBy($user);
            $entity->setUpdatedBy($user);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof AuditableInterface) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user) {
            $entity->setUpdatedBy($user);
        }
    }
}
