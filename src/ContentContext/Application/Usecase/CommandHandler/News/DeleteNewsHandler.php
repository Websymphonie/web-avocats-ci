<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Command\News\DeleteNewsCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(DeleteNewsCommand $command): void { $news = $this->repository->getById($command->id); $this->repository->delete($news); $this->eventPublisher?->publish('NEWS', 'DELETED', $news->uuid, $news->title, $news->slug); }
}
