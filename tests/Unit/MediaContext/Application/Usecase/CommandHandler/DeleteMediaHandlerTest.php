<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\MediaContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Application\Usecase\Command\DeleteMediaCommand;
use Websymphonie\MediaContext\Application\Usecase\CommandHandler\DeleteMediaHandler;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;

final class DeleteMediaHandlerTest extends TestCase
{
    public function testDelegatesExplicitDeletionToUsageCheckedMediaService(): void
    {
        $media = new Media(42, 'uuid', 'image.jpg', 'safe.jpg', 'image/jpeg', 1, 1, 1, 'uploads/galleries/safe.jpg');
        $repository = $this->createMock(MediaRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->with(42)->willReturn($media);
        $uploadService = $this->createMock(MediaUploadServiceInterface::class);
        $uploadService->expects(self::once())->method('delete')->with($media);
        (new DeleteMediaHandler($repository, $uploadService))(new DeleteMediaCommand(42));
    }
}
