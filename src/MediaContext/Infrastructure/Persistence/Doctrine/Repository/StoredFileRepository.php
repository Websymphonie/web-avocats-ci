<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Factory\StoredFileFactory;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<StoredFileEntity> */
final class StoredFileRepository extends ServiceEntityRepository implements StoredFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly StoredFileFactory $factory) { parent::__construct($registry, StoredFileEntity::class); }
    public function save(StoredFile $file): StoredFile
    {
        $entity = $file->id > 0 ? $this->find($file->id) : null;
        $entity = $this->factory->toEntity($file, $entity instanceof StoredFileEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $file->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }
    public function getById(int $id): StoredFile
    {
        $entity = $this->find($id);
        if (!$entity instanceof StoredFileEntity) { throw new StoredFileNotFoundException(sprintf('Le fichier #%d est introuvable.', $id)); }
        return $this->factory->fromEntity($entity);
    }

    /**
     * @param list<int> $ids
     * @return list<StoredFile>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) { return []; }

        $entities = $this->createQueryBuilder('file')
            ->andWhere('file.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return array_map(fn (StoredFileEntity $entity): StoredFile => $this->factory->fromEntity($entity), $entities);
    }
    public function delete(StoredFile $file): void
    {
        $entity = $this->find($file->id);
        if (!$entity instanceof StoredFileEntity) { throw new StoredFileNotFoundException(); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }
}
