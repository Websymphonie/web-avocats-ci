<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\Command;

final class SubmitContactMessageCommand
{
    public function __construct(
        public string $fullName = '',
        public string $email = '',
        public ?string $phone = null,
        public string $subject = '',
        public string $message = '',
        public bool $consent = false,
        public ?string $ip = null,
    ) {
    }
}
