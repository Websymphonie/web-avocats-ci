<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\Query\Message;

use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;

final class GetContactMessageListQuery
{
    public function __construct(
        public ?ContactMessageDeliveryStatus $status = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
