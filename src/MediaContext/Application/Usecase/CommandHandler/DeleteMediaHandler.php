<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\CommandHandler;

use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Application\Usecase\Command\DeleteMediaCommand;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteMediaHandler implements CommandHandler
{
    public function __construct(private MediaRepositoryInterface $repository, private MediaUploadServiceInterface $uploadService) {}
    public function __invoke(DeleteMediaCommand $command): void { $this->uploadService->delete($this->repository->getById($command->id)); }
}
