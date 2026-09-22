<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\BulkDeleteEditorialVideoCategoriesCommand;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteEditorialVideoCategoriesHandler implements CommandHandler { public function __construct(private EditorialVideoCategoryRepositoryInterface $repository) {} public function __invoke(BulkDeleteEditorialVideoCategoriesCommand $command): void { $categories = $this->repository->findByIds(array_values(array_unique($command->ids))); foreach ($categories as $category) { $count = $this->repository->countVideoUsage($category->id); if ($count > 0) { throw EditorialVideoCategoryInUseException::withId($category->id, $count); } } foreach ($categories as $category) { $this->repository->delete($category); } } }
