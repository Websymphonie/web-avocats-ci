<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Infrastructure\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;

final class DbLogListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        DbLogListener::enable();
        parent::tearDown();
    }

    public function testPasswordChangesNeverExposeOldOrNewHash(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with(self::callback(function (string $message): bool {
                self::assertStringContainsString(SensitiveLogDataSanitizer::REDACTED, $message);
                self::assertStringNotContainsString('old-hash', $message);
                self::assertStringNotContainsString('new-hash', $message);

                return true;
            }));

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->method('getEntityChangeSet')->willReturn([
            'password' => ['old-hash', 'new-hash'],
        ]);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $listener = new DbLogListener($logger, new SensitiveLogDataSanitizer());
        $listener->postUpdate(new PostUpdateEventArgs(new \stdClass(), $entityManager));
    }

    public function testNestedSuppressionIsRestoredAfterAnException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $listener = new DbLogListener($logger, new SensitiveLogDataSanitizer());
        $entityManager = $this->createMock(EntityManagerInterface::class);

        try {
            DbLogListener::withoutLogging(static function (): void {
                throw new \RuntimeException('operation failed');
            });
        } catch (\RuntimeException) {
        }

        $listener->postPersist(new PostPersistEventArgs(new \stdClass(), $entityManager));
    }

    public function testNestedDisableCallsDoNotReenableTheListenerTooEarly(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $listener = new DbLogListener($logger, new SensitiveLogDataSanitizer());
        $entityManager = $this->createMock(EntityManagerInterface::class);

        DbLogListener::disable();
        DbLogListener::disable();
        $listener->postPersist(new PostPersistEventArgs(new \stdClass(), $entityManager));
        DbLogListener::enable();
        $listener->postPersist(new PostPersistEventArgs(new \stdClass(), $entityManager));
        DbLogListener::enable();
        $listener->postPersist(new PostPersistEventArgs(new \stdClass(), $entityManager));
    }
}
