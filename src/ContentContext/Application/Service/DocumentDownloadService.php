<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageInterface;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;

final readonly class DocumentDownloadService
{
    public function __construct(private DocumentPublicationRepositoryInterface $documents, private StoredFileRepositoryInterface $files, private StoredFileStorageInterface $storage) {}
    public function forId(int $id): DocumentDownload
    {
        return $this->resolve($this->documents->getById($id));
    }
    public function forUuid(string $uuid): DocumentDownload
    {
        return $this->resolve($this->documents->getByUuid($uuid));
    }
    private function resolve(\Websymphonie\ContentContext\Domain\Model\DocumentPublication $document): DocumentDownload
    {
        $file = $this->files->getById($document->storedFileId);
        return new DocumentDownload($this->storage->locate($file), $file->originalName, $file->mimeType);
    }
}
