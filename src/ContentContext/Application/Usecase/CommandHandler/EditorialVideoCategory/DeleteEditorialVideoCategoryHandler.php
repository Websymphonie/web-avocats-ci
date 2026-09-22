<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\DeleteEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategoryInUseException;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteEditorialVideoCategoryHandler implements CommandHandler { public function __construct(private EditorialVideoCategoryRepositoryInterface $repository) {} public function __invoke(DeleteEditorialVideoCategoryCommand $command): void { $category = $this->repository->getById($command->id); $count = $this->repository->countVideoUsage($category->id); if ($count > 0) { throw EditorialVideoCategoryInUseException::withId($category->id, $count); } $this->repository->delete($category); } }
