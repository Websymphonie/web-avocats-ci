<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Users;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Domain\Exception\User\UserNotFoundException;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<User>
 *
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserModelRepositoryInterface
{
    public function __construct(
        ManagerRegistry                    $registry,
        private readonly ManagersInterface $manager,
        private readonly UserFactory       $factory,
    )
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function create(User $entity): User
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function update(User $entity): User
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function remove(User $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function getByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()->getOneOrNullResult();
    }

    public function getById(int $id): User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$user) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    /**
     * @param string $uuid
     * @return User|null
     */
    public function getByUuid(string $uuid): ?User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.uuid = :uuid')
            ->setParameter('uuid', $uuid, UuidType::NAME)
            ->getQuery()->getOneOrNullResult();
        if (!$user) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    /**
     * @param list<string> $uuids
     * @return list<User>
     */
    public function findByUuids(array $uuids): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $or = $queryBuilder->expr()->orX();
        $validUuidCount = 0;

        foreach ($uuids as $index => $uuid) {
            if (!Uuid::isValid($uuid)) {
                continue;
            }

            $parameter = 'user_uuid_' . $index;
            $or->add('u.uuid = :' . $parameter);
            $queryBuilder->setParameter($parameter, $uuid, UuidType::NAME);
            ++$validUuidCount;
        }

        if ($validUuidCount === 0) {
            return [];
        }

        return $queryBuilder->where($or)->getQuery()->getResult();
    }

    /**
     * @param list<int> $ids
     * @return list<User>
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<string> $roles
     * @return list<User>
     */
    public function findByRoles(array $roles): array
    {
        $qb = $this->createQueryBuilder('u');
        $expr = $qb->expr();

        $orX = $expr->orX();
        foreach ($roles as $index => $role) {
            $orX->add($expr->like('u.roles', ":role_$index"));
            $qb->setParameter("role_$index", '%' . $role . '%');
        }

        return $qb->where($orX)->getQuery()->getResult();
    }

    public function createCommandProfileFromUser(int $id): UpdateProfileCommand
    {
        $model = $this->createBaseCommandData($id);

        return new UpdateProfileCommand(
            id: $model->id,
            name: $model->name,
            email: $model->email,
        );
    }

    private function createBaseCommandData(int $id): UserModel
    {
        $user = $this->find($id);

        if ($user === null) {
            throw UserNotFoundException::withId($id);
        }

        return $this->factory->fromEntity($user);
    }

    public function createCommandFromUser(int $id): UpdateUserCommand
    {
        $model = $this->createBaseCommandData($id);

        return new UpdateUserCommand(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            roles: $model->roles,
            enabled: $model->enabled,
        );
    }

    /** @return Query<mixed, mixed> */
    public function getUserQuery(GetUserListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('u');
        if ($query->name !== null) {
            $qb->andWhere('u.name LIKE :name')
                ->setParameter('name', '%' . $query->name . '%');
        }
        if ($query->email !== null) {
            $qb = $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $query->email . '%');
        }
        if ($query->role !== null) {
            $qb->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
                ->setParameter('role', '"' . $query->role . '"');
        }

        if ($query->enabled !== null) {
            if ($query->enabled) {
                $qb = $qb->andWhere('u.enabled = true');
            } else {
                $qb = $qb->andWhere('u.enabled = false');
            }
        }

        $qb = $this->checkRoles($qb);
        return $qb->orderBy('u.updatedAt', 'DESC')->getQuery();
    }

    private function checkRoles(QueryBuilder $query): QueryBuilder
    {
        return $query
            ->andWhere('NOT JSON_CONTAINS(u.roles, :roleSuperAdmin) = 1')
            ->setParameter('roleSuperAdmin', '"ROLE_SUPER_ADMIN"');
    }

    public function countAll(bool $onlyDisabled = false): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('JSON_CONTAINS(u.roles, :roleSuperAdmin) = 0')
            ->setParameter('roleSuperAdmin', json_encode('ROLE_SUPER_ADMIN'));

        if ($onlyDisabled) {
            $qb->andWhere('u.enabled = :enabled')
                ->setParameter('enabled', false); // ou 0 si ton champ est un INT
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
