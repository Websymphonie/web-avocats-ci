<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\StoredFileInUseException;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;

final readonly class LocalStoredFileUploadService implements StoredFileUploadServiceInterface
{
    public function __construct(private StoredFileStorageInterface $storage, private StoredFileRepositoryInterface $repository, private StoredFileUsageCheckerInterface $usageChecker) {}
    public function upload(UploadedFile $file): StoredFile
    {
        $stored = $this->storage->store($file);
        $model = new StoredFile(0, '', $stored->originalName, $stored->storageName, $stored->mimeType, $stored->size, $stored->checksum);
        try { return $this->repository->save($model); } catch (\Throwable $exception) { try { $this->storage->delete($model); } catch (\Throwable) {} throw $exception; }
    }
    public function delete(StoredFile $file): void
    {
        if ($this->usageChecker->isUsed($file->id)) { throw new StoredFileInUseException('Ce document est encore utilisé par une publication.'); }
        $this->storage->delete($file);
        $this->repository->delete($file);
    }
}
