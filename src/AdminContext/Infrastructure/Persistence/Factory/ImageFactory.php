<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Factory;

use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\SharedContext\Presenter\Service\Image\ImageHelperInterface;

final class ImageFactory
{
    /**
     * @param list<Images> $entities
     * @param ImageHelperInterface|null $imageHelper
     * @return list<ImageModel>
     */
    public static function fromEntityList(array $entities, ?ImageHelperInterface $imageHelper): array
    {
        return array_map(fn(Images $image) => self::fromEntity($image, $imageHelper), $entities);
    }

    /**
     * @param Images|null $image
     * @param ImageHelperInterface|null $imageHelper
     * @return ImageModel|null
     */
    public static function fromEntity(?Images $image, ?ImageHelperInterface $imageHelper): ?ImageModel
    {
        if ($image === null) {
            return null;
        }
        $url = $image->getFilename() !== null ? $imageHelper->vichImageResolver(
            entity: $image,
        ) : $imageHelper->getDefaultImagePath();
        return new ImageModel(
            id: $image->getId(),
            name: $image->getName(),
            label: $image->getLabel(),
            url: $url,
            updatedAt: $image->getUpdatedAt(),
        );
    }
}
