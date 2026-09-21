<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\LiveStreamProvider;

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
        public ?LiveStreamProvider $streamProvider = null,
        public ?string $externalStreamId = null,
        public ?string $videoEmbedUrl = null,
    ) {
    }
}
