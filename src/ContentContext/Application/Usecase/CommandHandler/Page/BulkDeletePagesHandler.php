<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\BulkDeletePagesCommand;
use Websymphonie\ContentContext\Domain\Exception\PageNotFoundException;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeletePagesHandler implements CommandHandler
{
    public function __construct(private PageRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {}

    public function __invoke(BulkDeletePagesCommand $command): void
    {
        $ids = array_values(array_unique(array_filter($command->ids, static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return;
        }

        $pages = $this->repository->findByIds($ids);
        if (count($pages) !== count($ids)) {
            throw new PageNotFoundException('Une ou plusieurs pages sélectionnées sont introuvables. Aucune suppression n’a été effectuée.');
        }

        $this->repository->deleteMany($pages);

        foreach ($pages as $page) {
            $this->eventPublisher?->publish('PAGE', 'DELETED', $page->uuid, $page->title, $page->slug);
        }
    }
}
