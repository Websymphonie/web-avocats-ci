<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Csv;

use DateTimeInterface;
use InvalidArgumentException;

final readonly class CsvCell
{
    private function __construct(
        private CsvCellType $type,
        private int|float|string|null $value,
    ) {
    }

    public static function text(?string $value): self
    {
        return new self(CsvCellType::TEXT, $value);
    }

    public static function number(int|float $value): self
    {
        if (is_float($value) && !is_finite($value)) {
            throw new InvalidArgumentException('A CSV number must be finite.');
        }

        return new self(CsvCellType::NUMBER, $value);
    }

    public static function date(DateTimeInterface|string|null $value): self
    {
        return new self(CsvCellType::DATE, $value instanceof DateTimeInterface ? $value->format('Y-m-d') : $value);
    }

    public function render(): string
    {
        if ($this->value === null) {
            return '';
        }

        if ($this->type === CsvCellType::NUMBER) {
            return (string) $this->value;
        }

        $value = (string) $this->value;

        if ($this->type === CsvCellType::TEXT) {
            return self::neutralizeFormula($value);
        }

        return $value;
    }

    private static function neutralizeFormula(string $value): string
    {
        $firstDataCharacter = strspn($value, " \t\r\n\0\x0B");
        $first = $value[$firstDataCharacter] ?? '';

        if ($first !== '' && str_contains('=+-@', $first)) {
            return "'" . $value;
        }

        return $value;
    }
}
