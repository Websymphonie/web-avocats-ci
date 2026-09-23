<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile\LawyerProfileRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity(repositoryClass: LawyerProfileRepository::class)]
#[ORM\Table(name: 'lawyer_profile')]
#[ORM\Index(name: 'idx_lawyer_directory_visible', columns: ['directory_visible'])]
#[ORM\HasLifecycleCallbacks]
class LawyerProfileEntity
{
    use IdTrait;
    use DatesTrait;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom professionnel est obligatoire.')]
    #[Assert\Length(max: 255)]
    private string $displayName = '';

    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: true)]
    private ?Uuid $legacySourceUuid = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?User $user = null;
    #[ORM\ManyToOne(targetEntity: CabinetEntity::class)]
    #[ORM\JoinColumn(name: 'cabinet_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CabinetEntity $cabinet = null;
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $barNumber = null;
    #[ORM\Column(length: 40, options: ['default' => 'UNKNOWN'])]
    private string $professionalStatus = 'UNKNOWN';
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specializationSummary = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $directoryVisible = false;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $professionalEmail = null;
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $professionalPhone = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $portraitMediaId = null;

    #[ORM\PrePersist]
    public function initializeUuid(): void
    {
        $this->uuid ??= Uuid::v7();
    }

    public function getId(): ?int { return $this->id; }
    public function getUuid(): ?Uuid { return $this->uuid; }
    public function getUuidAsString(): ?string { return $this->uuid?->toRfc4122(); }
    public function getDisplayName(): string { return $this->displayName; }
    public function setDisplayName(string $value): self { $this->displayName = trim($value); return $this; }
    public function getLegacySourceUuid(): ?Uuid { return $this->legacySourceUuid; }
    public function setLegacySourceUuid(?Uuid $value): self { $this->legacySourceUuid = $value; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getCabinet(): ?CabinetEntity { return $this->cabinet; }
    public function setCabinet(?CabinetEntity $cabinet): self { $this->cabinet = $cabinet; return $this; }
    public function getBarNumber(): ?string { return $this->barNumber; }
    public function setBarNumber(?string $value): self { $this->barNumber = $value !== null ? trim($value) : null; return $this; }
    public function getProfessionalStatus(): string { return $this->professionalStatus; }
    public function setProfessionalStatus(string $value): self { $this->professionalStatus = $value; return $this; }
    public function getSpecializationSummary(): ?string { return $this->specializationSummary; }
    public function setSpecializationSummary(?string $value): self { $this->specializationSummary = $value !== null ? trim($value) : null; return $this; }
    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $value): self { $this->bio = $value !== null ? trim($value) : null; return $this; }
    public function isDirectoryVisible(): bool { return $this->directoryVisible; }
    public function setDirectoryVisible(bool $value): self { $this->directoryVisible = $value; return $this; }
    public function getProfessionalEmail(): ?string { return $this->professionalEmail; }
    public function setProfessionalEmail(?string $value): self { $trimmed = trim($value ?? ''); $this->professionalEmail = $trimmed !== '' ? $trimmed : null; return $this; }
    public function getProfessionalPhone(): ?string { return $this->professionalPhone; }
    public function setProfessionalPhone(?string $value): self { $trimmed = trim($value ?? ''); $this->professionalPhone = $trimmed !== '' ? $trimmed : null; return $this; }
    public function getPortraitMediaId(): ?int { return $this->portraitMediaId; }
    public function setPortraitMediaId(?int $value): self { $this->portraitMediaId = $value; return $this; }
}
                               
