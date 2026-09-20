<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\ArchivePhotoGalleryCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ArchivePhotoGalleryHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(ArchivePhotoGalleryCommand $command): PhotoGallery { $gallery = $this->repository->getById($command->id); $gallery->archive(); $gallery = $this->repository->save($gallery); $this->eventPublisher?->publish('PHOTO_GALLERY', 'ARCHIVED', $gallery->uuid, $gallery->title, $gallery->slug); return $gallery; }
}
