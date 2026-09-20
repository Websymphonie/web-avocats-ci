<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Command\Event\PublishEventCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class PublishEventHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(PublishEventCommand $command): void { $event = $this->repository->getById($command->id); $event->publish(); $event = $this->repository->save($event); $this->eventPublisher?->publish('EVENT', 'PUBLISHED', $event->uuid, $event->title, $event->slug, $event->publishedAt); }
}
