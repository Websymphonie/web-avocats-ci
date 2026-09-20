<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Command\Event\DeleteEventCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteEventHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(DeleteEventCommand $command): void { $event = $this->repository->getById($command->id); $this->repository->delete($event); $this->eventPublisher?->publish('EVENT', 'DELETED', $event->uuid, $event->title, $event->slug); }
}
