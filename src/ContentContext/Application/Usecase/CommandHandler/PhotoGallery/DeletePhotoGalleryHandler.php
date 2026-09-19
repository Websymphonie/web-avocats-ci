<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\DeletePhotoGalleryCommand;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Application\Service\PhotoGalleryUsageChecker;
use Websymphonie\ContentContext\Domain\Exception\PhotoGalleryInUseException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

/** Gallery deletion only detaches items; Media deletion is an explicit, usage-checked operation. */
final readonly class DeletePhotoGalleryHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private PhotoGalleryUsageChecker $usageChecker) {}
    public function __invoke(DeletePhotoGalleryCommand $command): void { if ($this->usageChecker->isUsed($command->id)) { throw new PhotoGalleryInUseException('Cette galerie est associée à une actualité ou un événement et ne peut pas être supprimée.'); } $this->repository->delete($this->repository->getById($command->id)); }
}
