<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Role;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Role\RolePermissionsEntity;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\RolePermissionsFactory;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<RolePermissionsEntity> */
final class RolePermissionsRepository extends ServiceEntityRepository implements RolePermissionsRepositoryInterface
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly ManagersInterface      $manager,
        private readonly RolePermissionsFactory $factory,
    )
    {
        parent::__construct($registry, RolePermissionsEntity::class);
    }

    public function getForRole(UserRolesEnum $role): ?RolePermissions
    {
        /** @var RolePermissionsEntity|null $entity */
        $entity = $this->findOneBy(['role' => $role->value]);

        return $entity === null ? null : $this->factory->fromEntity($entity);
    }

    /** @return list<RolePermissions> */
    public function list(): array
    {
        /** @var list<RolePermissionsEntity> $entities */
        $entities = $this->createQueryBuilder('rolePermissions')
            ->orderBy('rolePermissions.role', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->factory->fromEntityList($entities);
    }

    public function save(RolePermissions $rolePermissions): RolePermissions
    {
        $id = $rolePermissions->getId();
        $entity = $id === null ? $this->factory->toNewEntity($rolePermissions) : $this->find($id);

        if (!$entity instanceof RolePermissionsEntity) {
            throw new \LogicException('La configuration des permissions est introuvable.');
        }

        if ($id !== null) {
            $this->factory->updateEntity($entity, $rolePermissions);
        }

        $this->manager->execute($entity, $id === null ? DbActionEnum::NEW : DbActionEnum::EDIT);

        return $this->factory->fromEntity($entity);
    }
}
