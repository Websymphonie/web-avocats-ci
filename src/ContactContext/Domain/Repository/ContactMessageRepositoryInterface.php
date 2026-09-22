<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Repository;

use Websymphonie\ContactContext\Domain\Model\ContactMessage;

interface ContactMessageRepositoryInterface
{
    public function save(ContactMessage $message): ContactMessage;
}
