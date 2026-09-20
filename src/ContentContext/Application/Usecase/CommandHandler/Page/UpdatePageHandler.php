<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UpdatePageCommand;
use Websymphonie\ContentContext\Domain\Exception\PageSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdatePageHandler implements CommandHandler
{
    public function __construct(
        private PageRepositoryInterface $repository,
        private RichTextSanitizerInterface $sanitizer,
        private SluggerInterface $slugger,
    ) {
    }

    public function __invoke(UpdatePageCommand $command): Page
    {
        $page = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug(trim($command->slug !== '' ? $command->slug : $command->title))->toString());
        if ($this->repository->slugExists($slug, $page->id)) {
            throw new PageSlugAlreadyExistsException(sprintf('Le slug « %s » est déjà utilisé.', $slug));
        }

        $page->update(trim($command->title), $slug, $this->sanitizer->sanitize($command->content));
        return $this->repository->save($page);
    }
}
