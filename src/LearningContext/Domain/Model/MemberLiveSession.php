<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;

final readonly class MemberLiveSession
{
    public function __construct(
        public string $trainingUuid,
        public string $title,
        public ?int $coverMediaId,
        public ?string $categoryName,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
        public LiveDeliveryMode $deliveryMode,
        public ?string $location,
        public bool $canJoin,
    ) {
    }
}
