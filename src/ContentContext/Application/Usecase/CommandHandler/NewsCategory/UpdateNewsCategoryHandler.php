<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\NewsCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\UpdateNewsCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class UpdateNewsCategoryHandler implements CommandHandler
{
    public function __construct(private NewsCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(UpdateNewsCategoryCommand $command): void
    {
        $category = $this->repository->getById($command->id); $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString());
        if ($slug === '' || $this->repository->slugExists($slug, $category->id)) { throw NewsCategorySlugAlreadyExistsException::withSlug($slug ?: $name); }
        $category->update($name, $slug, $command->description ?: null); $this->repository->save($category);
    }
}
