<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Tag;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\CreateTagCommand;
use Websymphonie\ContentContext\Domain\Exception\TagSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class CreateTagHandler implements CommandHandler
{
    public function __construct(private TagRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(CreateTagCommand $command): Tag
    {
        $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw TagSlugAlreadyExistsException::withSlug($slug ?: $name); }
        return $this->repository->save(new Tag(0, '', $name, $slug));
    }
}
