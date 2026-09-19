<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\DeletePhotoGalleryCommand;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

/** Gallery deletion only detaches items; Media deletion is an explicit, usage-checked operation. */
final readonly class DeletePhotoGalleryHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository) {}
    public function __invoke(DeletePhotoGalleryCommand $command): void { $this->repository->delete($this->repository->getById($command->id)); }
}
