<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\PagePerson;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;
use Websymphonie\ContentContext\Domain\Repository\PagePersonGroupRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonGroupEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\PagePersonGroupFactory;

/** @extends ServiceEntityRepository<PagePersonGroupEntity> */
final class PagePersonGroupRepository extends ServiceEntityRepository implements PagePersonGroupRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly PagePersonGroupFactory $factory)
    {
        parent::__construct($registry, PagePersonGroupEntity::class);
    }

    /** @return list<PagePersonGroup> */
    public function listByPageId(int $pageId): array
    {
        $entities = $this->createQueryBuilder('personGroup')
            ->leftJoin('personGroup.entries', 'entry')->addSelect('entry')
            ->andWhere('IDENTITY(personGroup.page) = :pageId')->setParameter('pageId', $pageId)
            ->orderBy('personGroup.sortOrder', 'ASC')->addOrderBy('personGroup.title', 'ASC')
            ->addOrderBy('entry.sortOrder', 'ASC')->addOrderBy('entry.displayName', 'ASC')
            ->getQuery()->getResult();

        return array_map(fn (PagePersonGroupEntity $entity): PagePersonGroup => $this->factory->fromEntity($entity), $entities);
    }

    public function synchronizeForPage(int $pageId, array $groups): void
    {
        $entityManager = $this->getEntityManager();
        $page = $entityManager->find(PageEntity::class, $pageId);
        if (!$page instanceof PageEntity) {
            throw new \RuntimeException('La page à modifier est introuvable.');
        }

        $existingGroups = [];
        foreach ($this->createQueryBuilder('personGroup')->leftJoin('personGroup.entries', 'entry')->addSelect('entry')
            ->andWhere('IDENTITY(personGroup.page) = :pageId')->setParameter('pageId', $pageId)->getQuery()->getResult() as $existingGroup) {
            if ($existingGroup instanceof PagePersonGroupEntity) {
                $existingGroups[$existingGroup->getKey()] = $existingGroup;
            }
        }

        $requestedKeys = array_map(static fn (PagePersonGroup $group): string => $group->key, $groups);
        foreach ($existingGroups as $key => $group) {
            if (!in_array($key, $requestedKeys, true)) {
                $page->removePersonGroup($group);
                $entityManager->remove($group);
            }
        }

        foreach ($groups as $group) {
            $entity = $this->factory->toEntity($group, $page, $existingGroups[$group->key] ?? null);
            $page->addPersonGroup($entity);
            $entityManager->persist($entity);
        }

        $entityManager->flush();
    }

    public function countMediaUsage(int $mediaId): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(entry.id)')
            ->from(\Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonEntryEntity::class, 'entry')
            ->where('entry.portraitMediaId = :mediaId')->setParameter('mediaId', $mediaId)
            ->getQuery()->getSingleScalarResult();
    }
}
