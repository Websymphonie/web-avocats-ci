<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Command\Document\PublishDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class PublishDocumentPublicationHandler implements CommandHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(PublishDocumentPublicationCommand $command): DocumentPublication { $document = $this->repository->getById($command->id); $document->publish(); $document = $this->repository->save($document); $this->eventPublisher?->publish('DOCUMENT', 'PUBLISHED', $document->uuid, $document->title, $document->slug, $document->publishedAt); return $document; }
}
