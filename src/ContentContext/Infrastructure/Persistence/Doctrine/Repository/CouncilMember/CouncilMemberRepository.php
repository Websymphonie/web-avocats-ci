<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\CouncilMember;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Exception\CouncilMemberNotFoundException;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\CouncilMemberFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<CouncilMemberEntity> */
final class CouncilMemberRepository extends ServiceEntityRepository implements CouncilMemberRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly CouncilMemberFactory $factory)
    {
        parent::__construct($registry, CouncilMemberEntity::class);
    }

    public function save(CouncilMember $member): CouncilMember
    {
        $entity = $member->id > 0 ? $this->find($member->id) : null;
        $entity = $this->factory->toEntity($member, $entity instanceof CouncilMemberEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $member->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }

        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): CouncilMember
    {
        $entity = $this->find($id);
        if (!$entity instanceof CouncilMemberEntity) {
            throw CouncilMemberNotFoundException::withId($id);
        }

        return $this->factory->fromEntity($entity);
    }

    /** @return list<CouncilMember> */
    public function list(): array
    {
        return $this->map($this->createQueryBuilder('councilMember')
            ->addSelect('CASE WHEN councilMember.mandateEndedAt IS NULL THEN 0 ELSE 1 END AS HIDDEN currentOrder')
            ->addOrderBy('currentOrder', 'ASC')
            ->addOrderBy('councilMember.sortOrder', 'ASC')
            ->addOrderBy('councilMember.fullName', 'ASC')
            ->getQuery()->getResult());
    }

    /** @return list<CouncilMember> */
    public function listCurrent(): array
    {
        return $this->map($this->createQueryBuilder('councilMember')
            ->andWhere('councilMember.mandateEndedAt IS NULL')
            ->orderBy('councilMember.sortOrder', 'ASC')
            ->addOrderBy('councilMember.fullName', 'ASC')
            ->getQuery()->getResult());
    }

    public function countMediaUsage(int $mediaId): int
    {
        return (int) $this->createQueryBuilder('councilMember')
            ->select('COUNT(councilMember.id)')
            ->where('councilMember.portraitMediaId = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param list<CouncilMemberEntity> $entities
     * @return list<CouncilMember>
     */
    private function map(array $entities): array
    {
        return array_map(fn (CouncilMemberEntity $entity): CouncilMember => $this->factory->fromEntity($entity), $entities);
    }
}
