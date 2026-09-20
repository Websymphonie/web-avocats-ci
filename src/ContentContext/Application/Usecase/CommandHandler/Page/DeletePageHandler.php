<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\DeletePageCommand;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeletePageHandler implements CommandHandler
{
    public function __construct(private PageRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}

    public function __invoke(DeletePageCommand $command): void
    {
        $page = $this->repository->getById($command->id);
        $this->repository->delete($page);
        $this->eventPublisher?->publish('PAGE', 'DELETED', $page->uuid, $page->title, $page->slug);
    }
}
