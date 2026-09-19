<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\CommandHandler;

use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\MediaContext\Application\Usecase\Command\DeleteStoredFileCommand;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteStoredFileHandler implements CommandHandler
{
    public function __construct(private StoredFileRepositoryInterface $repository, private StoredFileUploadServiceInterface $service) {}
    public function __invoke(DeleteStoredFileCommand $command): void
    {
        $file = $this->repository->getById($command->id);
        $this->service->delete($file);
    }
}
