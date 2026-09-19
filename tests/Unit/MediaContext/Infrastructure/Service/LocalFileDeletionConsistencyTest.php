<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\MediaContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Websymphonie\MediaContext\Application\Service\MediaStorageInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\MediaContext\Infrastructure\Service\LocalMediaUploadService;
use Websymphonie\MediaContext\Infrastructure\Service\LocalStoredFileUploadService;

final class LocalFileDeletionConsistencyTest extends TestCase
{
    public function testMediaRowIsDeletedBeforeAStorageFailureIsLogged(): void
    {
        $media = new Media(42, 'uuid', 'image.jpg', 'safe.jpg', 'image/jpeg', 1, 1, 1, 'galleries/safe.jpg');
        $storage = $this->createMock(MediaStorageInterface::class);
        $storage->expects(self::once())->method('delete')->with($media)->willThrowException(new \RuntimeException('storage unavailable'));
        $repository = $this->createMock(MediaRepositoryInterface::class);
        $repository->expects(self::once())->method('delete')->with($media);
        $usageChecker = $this->createMock(MediaUsageCheckerInterface::class);
        $usageChecker->method('isUsed')->with(42)->willReturn(false);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        (new LocalMediaUploadService($storage, $repository, $usageChecker, $logger))->delete($media);
    }

    public function testStoredFileRowIsDeletedBeforeAStorageFailureIsLogged(): void
    {
        $file = new StoredFile(42, 'uuid', 'guide.pdf', 'documents/safe.pdf', 'application/pdf', 1, str_repeat('a', 64));
        $storage = $this->createMock(StoredFileStorageInterface::class);
        $storage->expects(self::once())->method('delete')->with($file)->willThrowException(new \RuntimeException('storage unavailable'));
        $repository = $this->createMock(StoredFileRepositoryInterface::class);
        $repository->expects(self::once())->method('delete')->with($file);
        $usageChecker = $this->createMock(StoredFileUsageCheckerInterface::class);
        $usageChecker->method('isUsed')->with(42)->willReturn(false);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        (new LocalStoredFileUploadService($storage, $repository, $usageChecker, $logger))->delete($file);
    }
}
