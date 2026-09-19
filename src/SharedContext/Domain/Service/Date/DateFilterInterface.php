<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Date;

use DateTimeImmutable;

interface DateFilterInterface
{
    public function getStart(): ?DateTimeImmutable;

    public function getEnd(): ?DateTimeImmutable;

    public function getYear(): ?int;
}