<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\UpdateEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class UpdateEditorialVideoCategoryHandler implements CommandHandler { public function __construct(private EditorialVideoCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(UpdateEditorialVideoCategoryCommand $command): void { $category = $this->repository->getById($command->id); $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug, $category->id)) { throw EditorialVideoCategorySlugAlreadyExistsException::withSlug($slug ?: $name); } $category->update($name, $slug, $command->description ?: null); $this->repository->save($category); } }
