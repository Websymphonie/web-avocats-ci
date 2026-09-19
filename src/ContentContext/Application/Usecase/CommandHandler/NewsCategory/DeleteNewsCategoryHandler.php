<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\NewsCategory;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\DeleteNewsCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteNewsCategoryHandler implements CommandHandler
{
    public function __construct(private NewsCategoryRepositoryInterface $repository) {}
    public function __invoke(DeleteNewsCategoryCommand $command): void
    {
        $category = $this->repository->getById($command->id); $count = $this->repository->countNewsUsage($category->id);
        if ($count > 0) { throw NewsCategoryInUseException::withId($category->id, $count); }
        $this->repository->delete($category);
    }
}
