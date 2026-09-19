<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\CommandHandler;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\MediaContext\Application\Usecase\Command\StoreStoredFileCommand;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class StoreStoredFileHandler implements CommandHandler
{
    public function __construct(private StoredFileUploadServiceInterface $service) {}
    public function __invoke(StoreStoredFileCommand $command): StoredFile { return $this->service->upload($command->file); }
}
