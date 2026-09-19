<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Repository\Image;

use Doctrine\ORM\Query;
use Websymphonie\AdminContext\Application\Usecase\Command\Image\UpdateImageCommand;
use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageListQuery;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;

interface ImageModelRepositoryInterface
{
    /**
     * @param ImageListQuery $query
     * @return array<ImageModel>
     */
    public function findALLForTwig(ImageListQuery $query): array;

    public function getValue(string $name): ?ImageModel;

    public function getValueEntity(string $name): ?Images;

    public function create(Images $entity): ImageModel;

    public function update(Images $entity): ImageModel;

    public function remove(Images $entity): void;

    public function getById(int $id): ImageModel;

    public function getByEntityId(int $id): Images;

    /** @return Query<mixed, mixed> */
    public function getImageQuery(ImageListQuery $query): Query;

    public function createCommandFromImage(int $id): UpdateImageCommand;
}
