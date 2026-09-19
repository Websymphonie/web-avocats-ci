<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Filter;

interface LastFilterInterface
{
    public function getLimit(): int;

    public function getYear(): ?int;
}