<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\CouncilMember\CouncilMemberRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: CouncilMemberRepository::class)]
#[ORM\Table(name: 'council_member')]
#[ORM\HasLifecycleCallbacks]
class CouncilMemberEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $fullName = '';

    #[ORM\Column(length: 255)]
    private string $function = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $portraitMediaId = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $mandateStartedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $mandateEndedAt = null;

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $value): self { $this->fullName = $value; return $this; }
    public function getFunction(): string { return $this->function; }
    public function setFunction(string $value): self { $this->function = $value; return $this; }
    public function getPortraitMediaId(): ?int { return $this->portraitMediaId; }
    public function setPortraitMediaId(?int $value): self { $this->portraitMediaId = $value; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $value): self { $this->sortOrder = $value; return $this; }
    public function getMandateStartedAt(): ?DateTimeImmutable { return $this->mandateStartedAt; }
    public function setMandateStartedAt(?DateTimeImmutable $value): self { $this->mandateStartedAt = $value; return $this; }
    public function getMandateEndedAt(): ?DateTimeImmutable { return $this->mandateEndedAt; }
    public function setMandateEndedAt(?DateTimeImmutable $value): self { $this->mandateEndedAt = $value; return $this; }
}
