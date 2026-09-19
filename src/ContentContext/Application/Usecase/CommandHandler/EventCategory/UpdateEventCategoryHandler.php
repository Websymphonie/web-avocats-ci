<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EventCategory;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\UpdateEventCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EventCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateEventCategoryHandler implements CommandHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(UpdateEventCategoryCommand $command): void { $category = $this->repository->getById($command->id); $slug = strtolower($this->slugger->slug($command->name)->toString()); if ($slug === '' || $this->repository->slugExists($slug, $category->id)) { throw EventCategorySlugAlreadyExistsException::withSlug($slug ?: $command->name); } $category->update(trim($command->name), $slug, $command->description ?: null); $this->repository->save($category); }
}
