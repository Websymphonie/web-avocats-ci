<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Command\Event\DeleteEventCommand;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteEventHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository) {}
    public function __invoke(DeleteEventCommand $command): void { $this->repository->delete($this->repository->getById($command->id)); }
}
