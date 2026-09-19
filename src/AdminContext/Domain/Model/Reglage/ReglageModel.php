<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Model\Reglage;

use DateTimeImmutable;

final class ReglageModel
{
    public function __construct(
        public ?int               $id = null,
        public ?string            $name = null,
        public ?string            $label = null,
        public ?string            $value = null,
        public ?string            $type = null,
        public ?string            $displayValue = null,
        public ?DateTimeImmutable $updatedAt = null,
    )
    {
    }
}
