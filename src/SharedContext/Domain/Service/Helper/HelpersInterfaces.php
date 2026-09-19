<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Helper;


interface HelpersInterfaces
{
    public function tocurrency(float $numberToConvert, ?string $devise = 'XOF'): string;

    public function towords(int $numberToConvert): string;

    /** @return array<int, int> */
    public function getYears(): array;

    public function currentYear(): int;
}
