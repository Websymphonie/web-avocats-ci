<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\BulkDeletePhotoGalleriesCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Application\Service\PhotoGalleryUsageChecker;
use Websymphonie\ContentContext\Domain\Exception\PhotoGalleryInUseException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeletePhotoGalleriesHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private PhotoGalleryUsageChecker $usageChecker, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(BulkDeletePhotoGalleriesCommand $command): void { $ids = array_values(array_unique(array_filter($command->ids, static fn ($id): bool => (int) $id > 0))); foreach ($ids as $id) { if ($this->usageChecker->isUsed((int) $id)) { throw new PhotoGalleryInUseException('Une galerie sélectionnée est associée à une actualité ou un événement et ne peut pas être supprimée.'); } } foreach ($ids as $id) { $gallery = $this->repository->getById((int) $id); $this->repository->delete($gallery); $this->eventPublisher?->publish('PHOTO_GALLERY', 'DELETED', $gallery->uuid, $gallery->title, $gallery->slug); } }
}
