<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Csv;

use InvalidArgumentException;

final readonly class CsvDownloadDefinition
{
    /**
     * @param list<string> $headers
     * @param iterable<array<int, CsvCell>> $rows
     */
    public function __construct(
        public string $filename,
        public array $headers,
        public iterable $rows,
    ) {
        self::assertSafeFilename($filename);

        foreach ($headers as $header) {
            if ($header === '' || str_contains($header, "\r") || str_contains($header, "\n")) {
                throw new InvalidArgumentException('CSV headers must be non-empty single-line strings.');
            }
        }
    }

    private static function assertSafeFilename(string $filename): void
    {
        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new InvalidArgumentException('CSV filename must not be empty or a path segment.');
        }

        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,179}\z/', $filename) !== 1) {
            throw new InvalidArgumentException('CSV filename contains invalid characters.');
        }
    }
}
