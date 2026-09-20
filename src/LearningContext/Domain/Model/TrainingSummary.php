<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class TrainingSummary
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public string $type,
        public string $status,
        public string $accessType,
    ) {
    }
}
