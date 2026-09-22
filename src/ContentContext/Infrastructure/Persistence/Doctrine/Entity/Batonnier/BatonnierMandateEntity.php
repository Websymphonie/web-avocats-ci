<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Batonnier\BatonnierMandateRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: BatonnierMandateRepository::class)]
#[ORM\Table(name: 'batonnier_mandate')]
#[ORM\UniqueConstraint(name: 'UNIQ_BATONNIER_CURRENT_MARKER', columns: ['current_marker'])]
#[ORM\HasLifecycleCallbacks]
class BatonnierMandateEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $fullName = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $portraitMediaId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $mandateStartedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $mandateEndedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    /** Technical nullable marker: the unique value 1 represents the current mandate. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $currentMarker = 1;

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $value): self { $this->fullName = $value; return $this; }
    public function getPortraitMediaId(): ?int { return $this->portraitMediaId; }
    public function setPortraitMediaId(?int $value): self { $this->portraitMediaId = $value; return $this; }
    public function getMandateStartedAt(): DateTimeImmutable { return $this->mandateStartedAt; }
    public function setMandateStartedAt(DateTimeImmutable $value): self { $this->mandateStartedAt = $value; return $this; }
    public function getMandateEndedAt(): ?DateTimeImmutable { return $this->mandateEndedAt; }
    public function setMandateEndedAt(?DateTimeImmutable $value): self { $this->mandateEndedAt = $value; $this->currentMarker = $value === null ? 1 : null; return $this; }
    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $value): self { $this->summary = $value; return $this; }
}
