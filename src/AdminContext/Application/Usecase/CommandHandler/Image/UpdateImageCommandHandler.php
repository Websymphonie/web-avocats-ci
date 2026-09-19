<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\CommandHandler\Image;

use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;
use Websymphonie\AdminContext\Application\Usecase\Command\Image\UpdateImageCommand;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Domain\Repository\Image\ImageModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

readonly class UpdateImageCommandHandler implements CommandHandler
{
    public function __construct(
        private ImageModelRepositoryInterface $repository,
        private StorageInterface              $storage,
        private PropertyMappingFactory        $mappingFactory,
    )
    {
    }

    public function __invoke(UpdateImageCommand $command): ImageModel
    {
        $image = $this->repository->getValueEntity($command->name);

        $image->setImageFile($command->imageFile);

        // Suppression des images si demandées
        if ($command->deleteFile) {
            $mapping = $this->mappingFactory->fromField($image, 'imageFile');
            if ($mapping) {
                $this->storage->remove($image, $mapping);
            }
            $image->setImageFile(null);
            $image->setFilename(null);
        }

        return $this->repository->update($image);
    }
}