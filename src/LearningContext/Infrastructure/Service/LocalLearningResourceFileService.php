<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\LearningContext\Application\Service\LearningResourceFileServiceInterface;
use Websymphonie\LearningContext\Application\Service\LearningResourceStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;

final readonly class LocalLearningResourceFileService implements LearningResourceFileServiceInterface
{
    public function __construct(private LearningResourceStorageInterface $storage, private StoredFileRepositoryInterface $repository, private StoredFileUsageCheckerInterface $usageChecker, private LoggerInterface $logger) {}

    public function upload(UploadedFile $file): StoredFile
    {
        $stored = $this->storage->store($file);
        $model = new StoredFile(0, '', $stored->originalName, $stored->storageName, $stored->mimeType, $stored->size, $stored->checksum);
        try { return $this->repository->save($model); } catch (\Throwable $exception) { try { $this->storage->delete($model); } catch (\Throwable) {} throw $exception; }
    }

    public function deleteIfOrphaned(int $storedFileId): void
    {
        if ($this->usageChecker->isUsed($storedFileId)) { return; }
        try {
            $file = $this->repository->getById($storedFileId);
            $this->repository->delete($file);
            try { $this->storage->delete($file); } catch (\Throwable $exception) { $this->logger->error('Le fichier Learning a été supprimé de la base mais son fichier physique doit être nettoyé.', ['stored_file_id' => $storedFileId, 'exception' => $exception]); }
        } catch (\Throwable $exception) {
            $this->logger->warning('Le nettoyage de la ressource Learning a échoué.', ['stored_file_id' => $storedFileId, 'exception' => $exception]);
        }
    }
}
