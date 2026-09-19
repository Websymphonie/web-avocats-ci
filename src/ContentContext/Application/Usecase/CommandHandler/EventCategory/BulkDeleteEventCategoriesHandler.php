<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EventCategory;

use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\BulkDeleteEventCategoriesCommand;
use Websymphonie\ContentContext\Domain\Exception\EventCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteEventCategoriesHandler implements CommandHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository) {}
    public function __invoke(BulkDeleteEventCategoriesCommand $command): void { $categories = $this->repository->findByIds(array_values(array_unique($command->ids))); foreach ($categories as $category) { $count = $this->repository->countEventUsage($category->id); if ($count > 0) { throw EventCategoryInUseException::withId($category->id, $count); } } foreach ($categories as $category) { $this->repository->delete($category); } }
}
