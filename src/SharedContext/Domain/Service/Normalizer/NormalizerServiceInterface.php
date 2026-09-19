<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Normalizer;

use DateTimeImmutable;

interface NormalizerServiceInterface
{
    public function normalizeDate(mixed $value): ?DateTimeImmutable;
}