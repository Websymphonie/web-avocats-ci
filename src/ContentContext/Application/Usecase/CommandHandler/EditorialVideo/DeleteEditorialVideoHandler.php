<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\DeleteEditorialVideoCommand;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteEditorialVideoHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository) {} public function __invoke(DeleteEditorialVideoCommand $command): void { $this->repository->delete($this->repository->getById($command->id)); } }
