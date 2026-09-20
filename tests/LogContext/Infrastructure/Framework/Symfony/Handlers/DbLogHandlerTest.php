<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Infrastructure\Framework\Symfony\Handlers;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;
use Websymphonie\LogContext\Infrastructure\Framework\Symfony\Handlers\DbLogHandler;

final class DbLogHandlerTest extends TestCase
{
    public function testItPersistsSanitizedScalarDataWithoutAnEntityManagerFlush(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                self::stringContains('INSERT INTO logs'),
                self::callback(function (array $params): bool {
                    self::assertStringNotContainsString('old-hash', (string) $params['message']);
                    self::assertStringNotContainsString('reset-secret', json_encode($params['context'], JSON_THROW_ON_ERROR));
                    self::assertStringNotContainsString('KKIAPAY_SECRET_KEY_VALUE', json_encode($params['extra'], JSON_THROW_ON_ERROR));
                    self::assertSame(42, $params['user_id']);

                    return true;
                }),
                self::anything(),
            )
            ->willReturn(1);

        $fallback = $this->createMock(LoggerInterface::class);
        $fallback->expects(self::never())->method('error');

        $handler = new DbLogHandler($connection, new SensitiveLogDataSanitizer(), $fallback);
        $handler->handle(new LogRecord(
            new DateTimeImmutable(),
            'db',
            Level::Info,
            'password=old-hash',
            ['resetToken' => 'reset-secret'],
            [
                'user' => ['id' => 42],
                'KKIAPAY_SECRET_KEY' => 'KKIAPAY_SECRET_KEY_VALUE',
            ],
        ));
    }

    public function testSqlFailureUsesFallbackAndDoesNotEscape(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('executeStatement')->willThrowException(new \RuntimeException('database unavailable'));

        $fallback = $this->createMock(LoggerInterface::class);
        $fallback->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('persistence SQL'),
                ['exceptionClass' => \RuntimeException::class],
            );

        $handler = new DbLogHandler($connection, new SensitiveLogDataSanitizer(), $fallback);
        $handler->handle(new LogRecord(new DateTimeImmutable(), 'db', Level::Info, 'technical event'));

        self::assertTrue(true);
    }
}
