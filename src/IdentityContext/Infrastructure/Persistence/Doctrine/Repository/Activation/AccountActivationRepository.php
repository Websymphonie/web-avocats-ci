<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Activation;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation\AccountActivation;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

/** @extends ServiceEntityRepository<AccountActivation> */
final class AccountActivationRepository extends ServiceEntityRepository implements AccountActivationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountActivation::class);
    }

    public function create(AccountActivation $activation): AccountActivation
    {
        $em = $this->getEntityManager();
        $em->persist($activation);
        $em->flush();
        return $activation;
    }

    public function findBySelector(string $selector): ?AccountActivation
    {
        /** @var AccountActivation|null $activation */
        $activation = $this->findOneBy(['selector' => $selector]);
        return $activation;
    }

    public function invalidateForUser(User $user, DateTimeImmutable $at): void
    {
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.consumedAt', ':at')
            ->where('a.user = :user')
            ->andWhere('a.consumedAt IS NULL')
            ->setParameter('at', $at)
            ->setParameter('user', $user)
            ->getQuery()->execute();
    }

    public function activate(AccountActivation $activation, string $rawToken, string $hashedPassword, DateTimeImmutable $at): void
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();
        try {
            /** @var AccountActivation|null $locked */
            $locked = $em->find(AccountActivation::class, $activation->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($locked === null || !$locked->isUsable($at) || !$locked->matchesToken($rawToken)) {
                throw new \RuntimeException('Activation token unavailable.');
            }
            $user = $locked->getUser();
            if ($user->getEnabled() || $user->getAccountMustBeVerifedBefore() === null) {
                throw new \RuntimeException('Account already active.');
            }
            $user->setPassword($hashedPassword);
            $user->setEnabled(true);
            $user->setAccountMustBeVerifedBefore(null);
            $locked->consume($at);
            $em->flush();
            $em->commit();
        } catch (Throwable $exception) {
            $em->rollback();
            throw $exception;
        }
    }

}
