<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\Payment;

use DateTimeImmutable;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Repository\Payment\PaymentRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payment')]
#[ORM\UniqueConstraint(name: 'uniq_payment_user_idempotency', columns: ['user_id', 'idempotency_key'])]
#[ORM\UniqueConstraint(name: 'uniq_payment_provider_reference', columns: ['provider', 'provider_reference'])]
#[ORM\Index(columns: ['user_id'])]
#[ORM\Index(columns: ['training_id'])]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
class PaymentEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId = 0;
    #[ORM\Column(name: 'training_id', type: 'integer')]
    private int $trainingId = 0;
    #[ORM\ManyToOne(targetEntity: TrainingOfferEntity::class)]
    #[ORM\JoinColumn(name: 'training_offer_id', referencedColumnName: 'id', nullable: true, onDelete: 'RESTRICT')]
    private ?TrainingOfferEntity $trainingOffer = null;
    #[ORM\Column(name: 'training_offer_id', type: 'integer', nullable: true, insertable: false, updatable: false)]
    private ?int $trainingOfferId = null;
    #[ORM\Column(type: 'integer')]
    private int $amount = 0;
    #[ORM\Column(length: 3)]
    private string $currency = 'XOF';
    #[ORM\Column(enumType: PaymentStatus::class, length: 20)]
    private PaymentStatus $status = PaymentStatus::PENDING;
    #[ORM\Column(enumType: PaymentProvider::class, length: 20)]
    private PaymentProvider $provider = PaymentProvider::FAKE;
    #[ORM\Column(name: 'provider_reference', length: 255, nullable: true)]
    private ?string $providerReference = null;
    #[ORM\Column(name: 'idempotency_key', length: 128)]
    private string $idempotencyKey = '';
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $confirmedAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $failedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function syncTrainingOfferAssociation(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->trainingOfferId !== null && ($this->trainingOffer === null || $this->trainingOffer->getId() !== $this->trainingOfferId)) {
            $this->trainingOffer = $event->getObjectManager()->getReference(TrainingOfferEntity::class, $this->trainingOfferId);
        }
    }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $value): self { $this->userId = $value; return $this; }
    public function getTrainingId(): int { return $this->trainingId; }
    public function setTrainingId(int $value): self { $this->trainingId = $value; return $this; }
    public function getTrainingOfferId(): ?int { return $this->trainingOfferId; }
    public function setTrainingOfferId(?int $value): self { $this->trainingOfferId = $value; return $this; }
    public function getAmount(): int { return $this->amount; }
    public function setAmount(int $value): self { $this->amount = $value; return $this; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $value): self { $this->currency = strtoupper($value); return $this; }
    public function getStatus(): PaymentStatus { return $this->status; }
    public function setStatus(PaymentStatus $value): self { $this->status = $value; return $this; }
    public function getProvider(): PaymentProvider { return $this->provider; }
    public function setProvider(PaymentProvider $value): self { $this->provider = $value; return $this; }
    public function getProviderReference(): ?string { return $this->providerReference; }
    public function setProviderReference(?string $value): self { $this->providerReference = $value; return $this; }
    public function getIdempotencyKey(): string { return $this->idempotencyKey; }
    public function setIdempotencyKey(string $value): self { $this->idempotencyKey = $value; return $this; }
    public function getConfirmedAt(): ?DateTimeImmutable { return $this->confirmedAt; }
    public function setConfirmedAt(?DateTimeImmutable $value): self { $this->confirmedAt = $value; return $this; }
    public function getFailedAt(): ?DateTimeImmutable { return $this->failedAt; }
    public function setFailedAt(?DateTimeImmutable $value): self { $this->failedAt = $value; return $this; }
}
