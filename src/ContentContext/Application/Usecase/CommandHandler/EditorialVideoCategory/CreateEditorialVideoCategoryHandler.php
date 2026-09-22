<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\CreateEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategorySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class CreateEditorialVideoCategoryHandler implements CommandHandler { public function __construct(private EditorialVideoCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(CreateEditorialVideoCategoryCommand $command): EditorialVideoCategory { $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug)) { throw EditorialVideoCategorySlugAlreadyExistsException::withSlug($slug ?: $name); } return $this->repository->save(new EditorialVideoCategory(0, '', $name, $slug, $command->description ?: null)); } }
