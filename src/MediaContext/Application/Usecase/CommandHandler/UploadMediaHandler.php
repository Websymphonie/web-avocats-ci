<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\CommandHandler;

use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Application\Usecase\Command\UploadMediaCommand;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UploadMediaHandler implements CommandHandler
{
    public function __construct(private MediaUploadServiceInterface $uploadService) {}
    public function __invoke(UploadMediaCommand $command): Media { return $this->uploadService->upload($command->file); }
}
