<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Tag;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\DeleteTagCommand;
use Websymphonie\ContentContext\Domain\Exception\TagInUseException;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteTagHandler implements CommandHandler
{
    public function __construct(private TagRepositoryInterface $repository) {}
    public function __invoke(DeleteTagCommand $command): void
    {
        $tag = $this->repository->getById($command->id); $count = $this->repository->countNewsUsage($tag->id);
        if ($count > 0) { throw TagInUseException::withId($tag->id, $count); }
        $this->repository->delete($tag);
    }
}
