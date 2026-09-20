<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Command\Document\DeleteDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteDocumentPublicationHandler implements CommandHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository, private StoredFileRepositoryInterface $files, private StoredFileUploadServiceInterface $fileService, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(DeleteDocumentPublicationCommand $command): void
    {
        $document = $this->repository->getById($command->id);
        $this->repository->delete($document);
        if ($this->repository->countStoredFileUsage($document->storedFileId) === 0) { $this->fileService->delete($this->files->getById($document->storedFileId)); }
        $this->eventPublisher?->publish('DOCUMENT', 'DELETED', $document->uuid, $document->title, $document->slug);
    }
}
