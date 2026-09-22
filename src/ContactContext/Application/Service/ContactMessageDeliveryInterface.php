<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

use Websymphonie\ContactContext\Domain\Model\ContactMessage;

interface ContactMessageDeliveryInterface
{
    public function send(ContactMessage $message): void;
}
