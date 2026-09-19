<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Tag;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\BulkDeleteTagsCommand;
use Websymphonie\ContentContext\Domain\Exception\TagInUseException;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteTagsHandler implements CommandHandler
{
    public function __construct(private TagRepositoryInterface $repository) {}
    public function __invoke(BulkDeleteTagsCommand $command): void
    {
        $tags = $this->repository->findByIds(array_values(array_unique($command->ids)));
        foreach ($tags as $tag) { $count = $this->repository->countNewsUsage($tag->id); if ($count > 0) { throw TagInUseException::withId($tag->id, $count); } }
        foreach ($tags as $tag) { $this->repository->delete($tag); }
    }
}
