<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Tag;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\UpdateTagCommand;
use Websymphonie\ContentContext\Domain\Exception\TagSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class UpdateTagHandler implements CommandHandler
{
    public function __construct(private TagRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(UpdateTagCommand $command): void
    {
        $tag = $this->repository->getById($command->id); $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString());
        if ($slug === '' || $this->repository->slugExists($slug, $tag->id)) { throw TagSlugAlreadyExistsException::withSlug($slug ?: $name); }
        $tag->update($name, $slug); $this->repository->save($tag);
    }
}
