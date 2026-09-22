<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Entity\ContactMessage;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Repository\ContactMessage\ContactMessageRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
#[ORM\Table(name: 'contact_message')]
#[ORM\HasLifecycleCallbacks]
class ContactMessageEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 180)]
    private string $fullName = '';

    #[ORM\Column(length: 180)]
    private string $email = '';

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180)]
    private string $subject = '';

    #[ORM\Column(type: 'text')]
    private string $message = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $consentAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $submittedAt;

    #[ORM\Column(enumType: ContactMessageDeliveryStatus::class, length: 20)]
    private ContactMessageDeliveryStatus $deliveryStatus = ContactMessageDeliveryStatus::PENDING;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $value): self { $this->fullName = $value; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $value): self { $this->email = $value; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $value): self { $this->phone = $value; return $this; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $value): self { $this->subject = $value; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $value): self { $this->message = $value; return $this; }
    public function getConsentAt(): DateTimeImmutable { return $this->consentAt; }
    public function setConsentAt(DateTimeImmutable $value): self { $this->consentAt = $value; return $this; }
    public function getSubmittedAt(): DateTimeImmutable { return $this->submittedAt; }
    public function setSubmittedAt(DateTimeImmutable $value): self { $this->submittedAt = $value; return $this; }
    public function getDeliveryStatus(): ContactMessageDeliveryStatus { return $this->deliveryStatus; }
    public function setDeliveryStatus(ContactMessageDeliveryStatus $value): self { $this->deliveryStatus = $value; return $this; }
    public function getSentAt(): ?DateTimeImmutable { return $this->sentAt; }
    public function setSentAt(?DateTimeImmutable $value): self { $this->sentAt = $value; return $this; }
}
