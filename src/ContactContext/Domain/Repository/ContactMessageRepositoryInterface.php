<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Repository;

use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Model\ContactMessageListResult;

interface ContactMessageRepositoryInterface
{
    public function save(ContactMessage $message): ContactMessage;

    public function getByUuid(string $uuid): ContactMessage;

    public function claimForRetry(string $uuid): ContactMessage;

    public function list(?ContactMessageDeliveryStatus $status, int $page, int $limit): ContactMessageListResult;
}
