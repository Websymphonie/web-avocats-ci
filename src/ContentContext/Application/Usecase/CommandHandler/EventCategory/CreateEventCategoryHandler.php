<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EventCategory;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\CreateEventCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EventCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateEventCategoryHandler implements CommandHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(CreateEventCategoryCommand $command): EventCategory { $slug = strtolower($this->slugger->slug($command->name)->toString()); if ($slug === '' || $this->repository->slugExists($slug)) { throw EventCategorySlugAlreadyExistsException::withSlug($slug ?: $command->name); } return $this->repository->save(new EventCategory(0, '', trim($command->name), $slug, $command->description ?: null)); }
}
