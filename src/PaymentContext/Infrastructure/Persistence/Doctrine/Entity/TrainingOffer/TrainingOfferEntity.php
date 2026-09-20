<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Repository\TrainingOffer\TrainingOfferRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: TrainingOfferRepository::class)]
#[ORM\Table(name: 'training_offer')]
#[ORM\UniqueConstraint(name: 'uniq_training_offer_training', columns: ['training_id'])]
#[ORM\Index(columns: ['active'])]
class TrainingOfferEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(name: 'training_id', type: 'integer')]
    private int $trainingId = 0;
    #[ORM\Column(type: 'integer')]
    private int $amount = 0;
    #[ORM\Column(length: 3)]
    private string $currency = 'XOF';
    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function getTrainingId(): int { return $this->trainingId; }
    public function setTrainingId(int $value): self { $this->trainingId = $value; return $this; }
    public function getAmount(): int { return $this->amount; }
    public function setAmount(int $value): self { $this->amount = $value; return $this; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $value): self { $this->currency = strtoupper($value); return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $value): self { $this->active = $value; return $this; }
}
