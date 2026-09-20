<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Command\Event\BulkDeleteEventsCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteEventsHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(BulkDeleteEventsCommand $command): void { foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $event) { $this->repository->delete($event); $this->eventPublisher?->publish('EVENT', 'DELETED', $event->uuid, $event->title, $event->slug); } }
}
