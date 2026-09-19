<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

enum PdfFormat: string
{
    case PDF = 'A4';

    public static function fromExtension(string $extension): PdfFormat
    {
        return match ($extension) {
            'pdf' => self::PDF,
            default => null,
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::PDF => 'application/pdf',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::PDF => 'pdf',
        };
    }
}
