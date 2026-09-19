<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EventCategory;

use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\DeleteEventCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EventCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteEventCategoryHandler implements CommandHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository) {}
    public function __invoke(DeleteEventCategoryCommand $command): void { $category = $this->repository->getById($command->id); $count = $this->repository->countEventUsage($category->id); if ($count > 0) { throw EventCategoryInUseException::withId($category->id, $count); } $this->repository->delete($category); }
}
