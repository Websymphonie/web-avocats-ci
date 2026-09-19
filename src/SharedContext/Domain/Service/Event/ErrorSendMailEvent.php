<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Event;

final readonly class ErrorSendMailEvent
{
    public function __construct(private string $email, private string $errerTitle, private string $errerMessage)
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getErrorTitle(): string
    {
        return $this->errerTitle;
    }

    public function getErrorMessage(): string
    {
        return $this->errerMessage;
    }
}