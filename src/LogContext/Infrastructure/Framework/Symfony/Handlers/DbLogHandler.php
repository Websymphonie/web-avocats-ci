<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Framework\Symfony\Handlers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;

final class DbLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SensitiveLogDataSanitizer $sanitizer,
        private readonly LoggerInterface $fallbackLogger,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    )
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $context = $this->sanitizer->sanitize($record->context);
        $extra = $this->sanitizer->sanitize($record->extra);
        $context = is_array($context) ? $context : [];
        $extra = is_array($extra) ? $extra : [];
        $message = $this->sanitizer->sanitizeMessage($record->message);

        try {
            $this->connection->executeStatement(
                'INSERT INTO logs (message, context, level, level_name, extra, created_at, updated_at, uuid, user_id) VALUES (:message, :context, :level, :level_name, :extra, :created_at, :updated_at, :uuid, :user_id)',
                [
                    'message' => $message,
                    'context' => $context,
                    'level' => $record->level->value,
                    'level_name' => $record->level->name,
                    'extra' => $extra,
                    'created_at' => new \DateTimeImmutable(),
                    'updated_at' => new \DateTimeImmutable(),
                    'uuid' => Uuid::v7()->toBinary(),
                    'user_id' => $this->extractUserId($extra),
                ],
                [
                    'context' => Types::JSON,
                    'extra' => Types::JSON,
                    'created_at' => Types::DATETIME_IMMUTABLE,
                    'updated_at' => Types::DATETIME_IMMUTABLE,
                    'uuid' => Types::BINARY,
                    'user_id' => Types::INTEGER,
                ],
            );
        } catch (Throwable $exception) {
            try {
                $this->fallbackLogger->error('La persistence SQL d’un log technique a échoué.', [
                    'exceptionClass' => $exception::class,
                ]);
            } catch (Throwable) {
                // Le logging technique reste best-effort et ne doit jamais créer une boucle.
            }
        }
    }

    /** @param array<string|int, mixed> $extra */
    private function extractUserId(array $extra): ?int
    {
        $user = $extra['user'] ?? null;
        if (is_array($user) && isset($user['id']) && is_int($user['id'])) {
            return $user['id'];
        }

        return null;
    }
}
