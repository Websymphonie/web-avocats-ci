<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Command\Document\BulkDeleteDocumentPublicationsCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\DeleteDocumentPublicationCommand;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteDocumentPublicationsHandler implements CommandHandler
{
    public function __construct(private DeleteDocumentPublicationHandler $delete) {}
    public function __invoke(BulkDeleteDocumentPublicationsCommand $command): void { foreach (array_unique(array_filter($command->ids, static fn (int $id): bool => $id > 0)) as $id) { ($this->delete)(new DeleteDocumentPublicationCommand($id)); } }
}
