<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\ReorderPhotoGalleryItemsCommand;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReorderPhotoGalleryItemsHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository) {}
    public function __invoke(ReorderPhotoGalleryItemsCommand $command): PhotoGallery { $gallery = $this->repository->getById($command->id); $gallery->reorderItems($command->mediaIds); return $this->repository->save($gallery); }
}
