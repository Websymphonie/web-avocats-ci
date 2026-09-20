<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UnpublishPageCommand;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UnpublishPageHandler implements CommandHandler
{
    public function __construct(private PageRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}

    public function __invoke(UnpublishPageCommand $command): void
    {
        $page = $this->repository->getById($command->id);
        $page->unpublish();
        $page = $this->repository->save($page);
        $this->eventPublisher?->publish('PAGE', 'UNPUBLISHED', $page->uuid, $page->title, $page->slug);
    }
}
