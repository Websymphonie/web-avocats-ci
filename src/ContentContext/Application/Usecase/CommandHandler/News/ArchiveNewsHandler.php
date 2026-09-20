<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Command\News\ArchiveNewsCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ArchiveNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}
    public function __invoke(ArchiveNewsCommand $command): void { $news = $this->repository->getById($command->id); $news->archive(); $news = $this->repository->save($news); $this->eventPublisher?->publish('NEWS', 'ARCHIVED', $news->uuid, $news->title, $news->slug); }
}
