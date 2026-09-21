<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Domain\Exception\MediaNotFoundException;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Factory\MediaFactory;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<MediaEntity> */
final class MediaRepository extends ServiceEntityRepository implements MediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly MediaFactory $factory) { parent::__construct($registry, MediaEntity::class); }
    public function save(Media $media): Media
    {
        $entity = $media->id > 0 ? $this->find($media->id) : null;
        $entity = $this->factory->toEntity($media, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $media->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }
    public function getById(int $id): Media { $entity = $this->find($id); if (!$entity instanceof MediaEntity) { throw MediaNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }

    /** @param list<int> $ids @return list<Media> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map(
            fn (MediaEntity $entity): Media => $this->factory->fromEntity($entity),
            $this->createQueryBuilder('media')->andWhere('media.id IN (:ids)')->setParameter('ids', array_values(array_unique($ids)))->getQuery()->getResult(),
        );
    }

    public function delete(Media $media): void
    {
        $entity = $this->find($media->id);
        if (!$entity instanceof MediaEntity) { throw MediaNotFoundException::withId($media->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }
}
