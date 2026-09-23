<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Import;

final class LegacyPortraitDownloadException extends \RuntimeException
{
    public function __construct(public readonly string $stage, string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
