<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Command\Document\ArchiveDocumentPublicationCommand;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ArchiveDocumentPublicationHandler implements CommandHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository) {}
    public function __invoke(ArchiveDocumentPublicationCommand $command): DocumentPublication { $document = $this->repository->getById($command->id); $document->archive(); return $this->repository->save($document); }
}
