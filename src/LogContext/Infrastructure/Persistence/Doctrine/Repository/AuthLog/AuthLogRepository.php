<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuthLog;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogListQuery;
use Websymphonie\LogContext\Domain\Repository\AuthLog\AuthLogModelRepository;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<AuthLog>
 */
class AuthLogRepository extends ServiceEntityRepository implements AuthLogModelRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager)
    {
        parent::__construct($registry, AuthLog::class);
    }

    public function create(AuthLog $entity): AuthLog
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function update(AuthLog $entity): AuthLog
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function remove(AuthLog $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    /**
     * @param int $id
     * @return AuthLog|null
     */
    public function getById(int $id): ?AuthLog
    {
        return $this->createQueryBuilder('a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return Query<mixed, mixed> */
    public function getAuthLogQuery(GetAuthLogListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('a');
        if ($query->userIp !== null) {
            $qb = $qb->andWhere('a.userIP LIKE :userIP')
                ->setParameter('userIP', '%' . $query->userIp . '%');
        }
        if ($query->emailEntered !== null) {
            $qb = $qb->andWhere('a.emailEntered LIKE :emailEntered')
                ->setParameter('emailEntered', '%' . $query->emailEntered . '%');
        }

        return $qb->addOrderBy('a.id', 'DESC')->getQuery();
    }

    /**
     * @param string $emailEntered
     * @param string|null $userIP
     */
    public function addFailedAuthAttempt(
        string  $emailEntered,
        ?string $userIP
    ): void
    {
        $authAttempt = (new AuthLog($emailEntered, $userIP))->setIsSuccessFulAuth(false);
        $this->getEntityManager()->persist($authAttempt);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $emailEntered
     * @param string|null $userIP
     * @param bool $isRememberMeAuth
     */
    public function addSuccessFulAuthAttempt(
        string  $emailEntered,
        ?string $userIP,
        bool    $isRememberMeAuth = false
    ): void
    {
        $authAttempt = (new AuthLog($emailEntered, $userIP));
        $authAttempt
            ->setIsSuccessFulAuth(true)
            ->setIsRememberMeAuth($isRememberMeAuth);
        $this->getEntityManager()->persist($authAttempt);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $emailEntered
     * @param string|null $userIP
     * @param bool $isRememberMeAuth
     */
    public function addSuccessFulLogouthAttempt(
        string  $emailEntered,
        ?string $userIP,
        bool    $isRememberMeAuth = false
    ): void
    {
        $authAttempt = (new AuthLog($emailEntered, $userIP));
        $authAttempt
            ->setIsSuccessFulAuth(false)
            ->setDeauthenticatedAt(new DateTimeImmutable())
            ->setIsRememberMeAuth($isRememberMeAuth);
        $this->getEntityManager()->persist($authAttempt);
        $this->getEntityManager()->flush();
    }

}
