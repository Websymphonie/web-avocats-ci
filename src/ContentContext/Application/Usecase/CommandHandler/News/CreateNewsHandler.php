<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private SluggerInterface $slugger) {}

    public function __invoke(CreateNewsCommand $command): News
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) {
            throw NewsSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        return $this->repository->save(new News(0, '', trim($command->title), $slug, $command->excerpt ?: null, $command->body));
    }
}
