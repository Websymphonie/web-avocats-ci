<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;
use Websymphonie\AdminContext\Domain\Exception\NoDataFoundException;
use Websymphonie\AuthContext\Domain\Repository\Reset\ResetPasswordRepositoryInterface;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<ResetPassword>
 *
 */
class ResetPasswordRepository extends ServiceEntityRepository implements ResetPasswordRepositoryInterface
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly ManagersInterface      $manager,
        private readonly RememberMeTokenRevoker $rememberMeTokenRevoker,
    )
    {
        parent::__construct($registry, ResetPassword::class);
    }


    public function getById(int $id): ResetPassword
    {
        $resetPassword = $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$resetPassword) {
            throw new NoDataFoundException();
        }
        return $resetPassword;
    }

    /** @return ResetPassword|null */
    public function getBySelector(string $selector): ?ResetPassword
    {
        return $this->createQueryBuilder('r')
            ->where('r.selector = :selector')
            ->setParameter('selector', $selector)
            ->getQuery()->getOneOrNullResult();
    }

    public function saveIssued(ResetPassword $entity): ResetPassword
    {
        $em = $this->getEntityManager();
        $user = $entity->getUser();
        if ($user === null || $user->getId() === null) {
            throw new \RuntimeException('Reset user unavailable.');
        }
        $em->beginTransaction();
        try {
            $em->find(User::class, $user->getId(), LockMode::PESSIMISTIC_WRITE);
            $em->persist($entity);
            DbLogListener::disable();
            $em->flush();
            DbLogListener::enable();
            $em->commit();
        } catch (Throwable $exception) {
            DbLogListener::enable();
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            throw $exception;
        }
        return $entity;
    }

    public function consumeAndUpdate(ResetPassword $resetPassword, string $secret, string $hashedPassword, DateTimeImmutable $at): User
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();
        try {
            /** @var ResetPassword|null $locked */
            $locked = $em->find(ResetPassword::class, $resetPassword->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($locked === null || !$locked->matchesSecret($secret) || !$locked->isUsable($at)) {
                throw new \RuntimeException('Reset token unavailable.');
            }
            $user = $locked->getUser();
            if ($user === null || !$user->getEnabled()) {
                throw new \RuntimeException('Reset token unavailable.');
            }
            $user->setPassword($hashedPassword);
            $user->incrementSecurityVersion();
            $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($user->getUserIdentifier());
            $locked->consume($at);
            $locked->setLastResetPassword(new \DateTime());
            DbLogListener::disable();
            $em->flush();
            DbLogListener::enable();
            $em->commit();
            return $user;
        } catch (Throwable $exception) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            throw $exception;
        }
    }

    public function create(ResetPassword $entity): ResetPassword
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function update(ResetPassword $entity): ResetPassword
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function remove(ResetPassword $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }
}
