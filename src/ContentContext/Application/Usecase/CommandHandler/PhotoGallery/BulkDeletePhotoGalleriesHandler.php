<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\BulkDeletePhotoGalleriesCommand;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeletePhotoGalleriesHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository) {}
    public function __invoke(BulkDeletePhotoGalleriesCommand $command): void { foreach (array_values(array_unique(array_filter($command->ids, static fn ($id): bool => (int) $id > 0))) as $id) { $this->repository->delete($this->repository->getById((int) $id)); } }
}
