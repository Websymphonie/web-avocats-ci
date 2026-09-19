<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\NewsCategory;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\BulkDeleteNewsCategoriesCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteNewsCategoriesHandler implements CommandHandler
{
    public function __construct(private NewsCategoryRepositoryInterface $repository) {}
    public function __invoke(BulkDeleteNewsCategoriesCommand $command): void
    {
        $categories = $this->repository->findByIds(array_values(array_unique($command->ids)));
        foreach ($categories as $category) { $count = $this->repository->countNewsUsage($category->id); if ($count > 0) { throw NewsCategoryInUseException::withId($category->id, $count); } }
        foreach ($categories as $category) { $this->repository->delete($category); }
    }
}
