<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\NewsCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\CreateNewsCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class CreateNewsCategoryHandler implements CommandHandler
{
    public function __construct(private NewsCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {}
    public function __invoke(CreateNewsCategoryCommand $command): NewsCategory
    {
        $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw NewsCategorySlugAlreadyExistsException::withSlug($slug ?: $name); }
        return $this->repository->save(new NewsCategory(0, '', $name, $slug, $command->description ?: null));
    }
}
