<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Command\News\BulkDeleteNewsCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(BulkDeleteNewsCommand $command): void { foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $news) { $this->repository->delete($news); $this->eventPublisher?->publish('NEWS', 'DELETED', $news->uuid, $news->title, $news->slug); } }
}
