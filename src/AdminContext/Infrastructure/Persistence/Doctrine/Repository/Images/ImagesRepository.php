<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Images;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\AdminContext\Application\Usecase\Command\Image\UpdateImageCommand;
use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageListQuery;
use Websymphonie\AdminContext\Domain\Exception\ImageNotFound;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Domain\Repository\Image\ImageModelRepositoryInterface;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\ImageFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;
use Websymphonie\SharedContext\Presenter\Service\Image\ImageHelperInterface;

/**
 * @extends ServiceEntityRepository<Images>
 */
class ImagesRepository extends ServiceEntityRepository implements ImageModelRepositoryInterface
{
    public function __construct(
        ManagerRegistry                       $registry,
        private readonly ManagersInterface    $manager,
        private readonly ImageHelperInterface $helper,
    )
    {
        parent::__construct($registry, Images::class);
    }

    /**
     * @param ImageListQuery $query
     * @return array|ImageModel[]
     */
    public function findALLForTwig(ImageListQuery $query): array
    {
        $images = $this->createQueryBuilder('i', 'i.name')
            ->getQuery()
            ->getResult();

        return ImageFactory::fromEntityList($images, $this->helper);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getValue(string $name): ?ImageModel
    {
        $image = $this->createQueryBuilder('i')
            ->where('i.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$image instanceof Images) {
            $defaultParameter = match ($name) {
                'app_logo' => 'app.logo_default',
                'app_favicon' => 'app.favicon',
                default => 'app.image_default',
            };

            return new ImageModel(
                name: $name,
                label: $name,
                url: $this->helper->getDefaultImagePath($defaultParameter),
            );
        }

        return ImageFactory::fromEntity($image, $this->helper);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getValueEntity(string $name): ?Images
    {
        return $this->createQueryBuilder('i')
            ->where('i.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Images $entity
     * @return ImageModel
     */
    public function create(Images $entity): ImageModel
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return ImageFactory::fromEntity($entity, $this->helper);
    }

    /**
     * @param Images $entity
     * @return ImageModel
     */
    public function update(Images $entity): ImageModel
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return ImageFactory::fromEntity($entity, $this->helper);
    }

    /**
     * @param Images $entity
     * @return void
     */
    public function remove(Images $entity): void
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
     * @return ImageModel
     */
    public function getById(int $id): ImageModel
    {
        $image = $this->find($id);

        if ($image === null) {
            throw ImageNotFound::withId($id);
        }

        return ImageFactory::fromEntity($image, $this->helper);
    }

    /**
     * @param int $id
     * @return Images
     */
    public function getByEntityId(int $id): Images
    {
        $image = $this->find($id);

        if ($image === null) {
            throw ImageNotFound::withId($id);
        }

        return $image;
    }

    /**
     * @param ImageListQuery $query
     * @return Query
     */
    /** @return Query<mixed, mixed> */
    public function getImageQuery(ImageListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('i');
        return $qb->orderBy('i.updatedAt', 'DESC')->getQuery();
    }

    /**
     * @param int $id
     * @return UpdateImageCommand
     */
    public function createCommandFromImage(int $id): UpdateImageCommand
    {
        $image = $this->find($id);

        if ($image === null) {
            throw ImageNotFound::withId($id);
        }
        return new UpdateImageCommand(
            id: $image->getId(),
            name: $image->getName(),
            label: $image->getLabel(),
            filename: $image->getFilename(),
        );
    }
}
