<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Service;

use DateTimeInterface;
use JsonSerializable;

final class SensitiveLogDataSanitizer
{
    public const string REDACTED = '[REDACTED]';

    /** @var list<string> */
    private const array SENSITIVE_KEY_PARTS = [
        'password',
        'passwordhash',
        'hashedpassword',
        'plainpassword',
        'token',
        'resettoken',
        'activationtoken',
        'refreshtoken',
        'accesstoken',
        'secret',
        'secretkey',
        'privatekey',
        'apikey',
        'authorization',
        'cookie',
        'session',
        'csrf',
    ];

    public function isSensitiveKey(string $key): bool
    {
        $normalizedKey = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $key));

        foreach (self::SENSITIVE_KEY_PARTS as $sensitivePart) {
            if (str_contains($normalizedKey, $sensitivePart)) {
                return true;
            }
        }

        return false;
    }

    public function sanitizeMessage(string $message): string
    {
        foreach (self::SENSITIVE_KEY_PARTS as $sensitivePart) {
            $message = preg_replace(
                '/((?:' . preg_quote($sensitivePart, '/') . ')[a-z0-9_-]*\s*["\']?\s*[:=]\s*["\']?)([^\s,"\']+)/i',
                '$1' . self::REDACTED,
                $message
            ) ?? $message;
        }

        return $message;
    }

    /**
     * @return scalar|array<string|int, mixed>|null
     */
    public function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && $this->isSensitiveKey($key)) {
            return self::REDACTED;
        }

        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $itemKey => $item) {
                $sanitized[$itemKey] = $this->sanitize($item, is_string($itemKey) ? $itemKey : null);
            }

            return $sanitized;
        }

        if ($value instanceof JsonSerializable) {
            return $this->sanitize($value->jsonSerialize(), $key);
        }

        return sprintf('[object:%s]', $value::class);
    }

    public function stringify(mixed $value, ?string $key = null): string
    {
        $sanitized = $this->sanitize($value, $key);

        if (is_string($sanitized)) {
            return $sanitized;
        }

        if ($sanitized === null) {
            return 'null';
        }

        if (is_bool($sanitized)) {
            return $sanitized ? 'true' : 'false';
        }

        if (is_int($sanitized) || is_float($sanitized)) {
            return (string) $sanitized;
        }

        return json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
