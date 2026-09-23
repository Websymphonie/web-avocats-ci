<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile\LawyerProfileRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity(repositoryClass: LawyerProfileRepository::class)]
#[ORM\Table(name: 'lawyer_profile')]
#[ORM\HasLifecycleCallbacks]
class LawyerProfileEntity
{
    use IdTrait;
    use DatesTrait;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $user;
    #[ORM\ManyToOne(targetEntity: CabinetEntity::class)]
    #[ORM\JoinColumn(name: 'cabinet_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CabinetEntity $cabinet = null;
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $barNumber = null;
    #[ORM\Column(length: 40, options: ['default' => 'ACTIVE'])]
    private string $professionalStatus = 'ACTIVE';
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specializationSummary = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
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
}
                               
