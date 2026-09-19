<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\AddPhotoGalleryImagesCommand;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class AddPhotoGalleryImagesHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private MediaUploadServiceInterface $mediaUpload) {}
    public function __invoke(AddPhotoGalleryImagesCommand $command): PhotoGallery
    {
        $gallery = $this->repository->getById($command->id); $media = [];
        try { foreach ($command->images as $image) { $media[] = $this->mediaUpload->upload($image); } $gallery->addMedia(array_map(static fn ($asset): int => $asset->id, $media)); return $this->repository->save($gallery); }
        catch (\Throwable $exception) { foreach ($media as $asset) { try { $this->mediaUpload->delete($asset); } catch (\Throwable) {} } throw $exception; }
    }
}
