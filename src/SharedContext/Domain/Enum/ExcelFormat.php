<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

use InvalidArgumentException;

enum ExcelFormat: string
{
    case CSV = 'csv';
    case ODS = 'ods';
    case XLSX = 'xlsx';

    public const string CSV_LABEL = 'Fichier csv';
    public const string ODS_LABEL = 'Fichier OpenDocument';
    public const string XLSX_LABEL = 'Fichier excel';

    public static function fromExtension(string $extension): ExcelFormat
    {
        return match ($extension) {
            self::CSV->value => self::CSV,
            self::ODS->value => self::ODS,
            self::XLSX->value => self::XLSX,
            default => throw new InvalidArgumentException('Invalid file type'),
        };
    }

    public static function getText(string $value): string
    {
        return match ($value) {
            self::CSV->value => self::CSV_LABEL,
            self::ODS->value => self::ODS_LABEL,
            self::XLSX->value => self::XLSX_LABEL,
            default => throw new InvalidArgumentException('Invalid file type'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function getChoices(): array
    {
        return [
            self::CSV_LABEL => self::CSV->value,
            self::ODS_LABEL => self::ODS->value,
            self::XLSX_LABEL => self::XLSX->value,
        ];
    }

    public function contentType(): string
    {
        return match ($this) {
            self::CSV => 'text/csv',
            self::ODS => 'application/vnd.oasis.opendocument.spreadsheet',
            self::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::CSV => self::CSV->value,
            self::ODS => self::ODS->value,
            self::XLSX => self::XLSX->value,
        };
    }
}
