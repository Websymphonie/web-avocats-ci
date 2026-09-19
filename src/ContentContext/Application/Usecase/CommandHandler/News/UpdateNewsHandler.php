<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private SluggerInterface $slugger) {}

    public function __invoke(UpdateNewsCommand $command): void
    {
        $news = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($news->status->value === 'DRAFT' && ($slug === '' || $this->repository->slugExists($slug, $news->id))) {
            throw NewsSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        $news->update(trim($command->title), $slug ?: $news->slug, $command->excerpt ?: null, $command->body);
        $this->repository->save($news);
    }
}
