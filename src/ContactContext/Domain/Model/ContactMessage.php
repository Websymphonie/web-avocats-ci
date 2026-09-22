<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Exception\ContactMessageDeliveryRetryNotAllowedException;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class ContactMessage
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $fullName,
        public string $email,
        public ?string $phone,
        public string $subject,
        public string $message,
        public DateTimeImmutable $consentAt,
        public DateTimeImmutable $submittedAt,
        public ContactMessageDeliveryStatus $deliveryStatus = ContactMessageDeliveryStatus::PENDING,
        public ?DateTimeImmutable $sentAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->fullName = self::required($fullName, 'Le nom est obligatoire.');
        $this->email = self::required($email, 'L’adresse email est obligatoire.');
        $this->subject = self::required($subject, 'Le sujet est obligatoire.');
        $this->message = self::required($message, 'Le message est obligatoire.');
        $this->phone = self::optional($phone);
    }

    public function markSent(DateTimeImmutable $sentAt): void
    {
        $this->deliveryStatus = ContactMessageDeliveryStatus::SENT;
        $this->sentAt = $sentAt;
    }

    public function markFailed(): void
    {
        $this->deliveryStatus = ContactMessageDeliveryStatus::FAILED;
        $this->sentAt = null;
    }

    public function prepareForRetry(): void
    {
        if ($this->deliveryStatus !== ContactMessageDeliveryStatus::FAILED) {
            throw new ContactMessageDeliveryRetryNotAllowedException();
        }

        $this->deliveryStatus = ContactMessageDeliveryStatus::PENDING;
        $this->sentAt = null;
    }

    private static function required(string $value, string $message): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgument($message);
        }

        return $value;
    }

    private static function optional(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);
        return $value === '' ? null : $value;
    }
}
