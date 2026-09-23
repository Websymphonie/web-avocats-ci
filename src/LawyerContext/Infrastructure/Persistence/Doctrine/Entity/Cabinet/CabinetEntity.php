<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet\CabinetRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: CabinetRepository::class)]
#[ORM\Table(name: 'cabinet')]
#[ORM\Index(name: 'idx_cabinet_directory_visible', columns: ['directory_visible'])]
#[ORM\HasLifecycleCallbacks]
class CabinetEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $name = '';
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: true)]
    private ?Uuid $legacySourceUuid = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $city = null;
    #[ORM\Column(length: 120, options: ['default' => 'Côte d’Ivoire'])]
    private string $country = 'Côte d’Ivoire';
    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $phones = [];
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteUrl = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(length: 20, options: ['default' => 'ACTIVE'])]
    private string $status = 'ACTIVE';
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $directoryVisible = false;

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = trim($value); return $this; }
    public function getLegacySourceUuid(): ?Uuid { return $this->legacySourceUuid; }
    public function setLegacySourceUuid(?Uuid $value): self { $this->legacySourceUuid = $value; return $this; }
    public function getRegistrationNumber(): ?string { return $this->registrationNumber; }
    public function setRegistrationNumber(?string $value): self { $this->registrationNumber = $value !== null ? trim($value) : null; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $value): self { $this->address = $value !== null ? trim($value) : null; return $this; }
    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $value): self { $this->city = $value !== null ? trim($value) : null; return $this; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $value): self { $this->country = trim($value); return $this; }
    /** @return list<string> */
    public function getPhones(): array { return $this->phones; }
    /** @param list<string> $values */
    public function setPhones(array $values): self
    {
        $this->phones = array_values(array_filter(array_map(static fn (string $value): string => trim($value), $values), static fn (string $value): bool => $value !== ''));
        return $this;
    }
    public function getPhone(): ?string { return $this->phones[0] ?? null; }
    public function setPhone(?string $value): self { return $this->setPhones($value !== null && trim($value) !== '' ? [$value] : []); }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $value): self { $this->email = $value !== null ? trim($value) : null; return $this; }
    public function getWebsiteUrl(): ?string { return $this->websiteUrl; }
    public function setWebsiteUrl(?string $value): self { $this->websiteUrl = $value !== null ? trim($value) : null; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $value): self { $this->description = $value !== null ? trim($value) : null; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $value): self { $this->status = $value; return $this; }
    public function isActive(): bool { return $this->status === 'ACTIVE'; }
    public function isDirectoryVisible(): bool { return $this->directoryVisible; }
    public function setDirectoryVisible(bool $value): self { $this->directoryVisible = $value; return $this; }
    public function __toString(): string { return $this->name; }
}
                                 
