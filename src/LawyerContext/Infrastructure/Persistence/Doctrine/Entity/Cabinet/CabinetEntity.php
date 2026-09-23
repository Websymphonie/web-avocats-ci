<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet\CabinetRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: CabinetRepository::class)]
#[ORM\Table(name: 'cabinet')]
#[ORM\HasLifecycleCallbacks]
class CabinetEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $name = '';
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $city = null;
    #[ORM\Column(length: 120, options: ['default' => 'Côte d’Ivoire'])]
    private string $country = 'Côte d’Ivoire';
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $phone = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteUrl = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(length: 20, options: ['default' => 'ACTIVE'])]
    private string $status = 'ACTIVE';

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = trim($value); return $this; }
    public function getRegistrationNumber(): ?string { return $this->registrationNumber; }
    public function setRegistrationNumber(?string $value): self { $this->registrationNumber = $value !== null ? trim($value) : null; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $value): self { $this->address = $value !== null ? trim($value) : null; return $this; }
    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $value): self { $this->city = $value !== null ? trim($value) : null; return $this; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $value): self { $this->country = trim($value); return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $value): self { $this->phone = $value !== null ? trim($value) : null; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $value): self { $this->email = $value !== null ? trim($value) : null; return $this; }
    public function getWebsiteUrl(): ?string { return $this->websiteUrl; }
    public function setWebsiteUrl(?string $value): self { $this->websiteUrl = $value !== null ? trim($value) : null; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $value): self { $this->description = $value !== null ? trim($value) : null; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $value): self { $this->status = $value; return $this; }
    public function isActive(): bool { return $this->status === 'ACTIVE'; }
    public function __toString(): string { return $this->name; }
}
                                 
