<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\MediaStorageInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;

final readonly class LocalMediaUploadService implements MediaUploadServiceInterface
{
    public function __construct(private MediaStorageInterface $storage, private MediaRepositoryInterface $repository, private MediaUsageCheckerInterface $usageChecker, private LoggerInterface $logger) {}

    public function upload(UploadedFile $file, string $storagePrefix = 'galleries'): Media
    {
        $stored = $this->storage->store($file, $storagePrefix);
        try {
            return $this->repository->save(new Media(0, '', $stored->originalName, $stored->storageName, $stored->mimeType, $stored->size, $stored->width, $stored->height, $stored->storagePath));
        } catch (\Throwable $exception) {
            $this->storage->delete(new Media(0, '', $stored->originalName, $stored->storageName, $stored->mimeType, $stored->size, $stored->width, $stored->height, $stored->storagePath));
            throw $exception;
        }
    }

    public function delete(Media $media): void
    {
        if ($this->usageChecker->isUsed($media->id)) { throw MediaInUseException::withId($media->id); }
        $this->repository->delete($media);
        try {
            $this->storage->delete($media);
        } catch (\Throwable $exception) {
            $this->logger->error('Le média a été supprimé de la base mais son fichier physique doit être nettoyé.', [
                'media_id' => $media->id,
                'storage_path' => $media->storagePath,
                'exception' => $exception,
            ]);
        }
    }
}
