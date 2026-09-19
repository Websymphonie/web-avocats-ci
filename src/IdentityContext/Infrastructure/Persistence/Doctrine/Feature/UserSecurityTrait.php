<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Feature;

use Doctrine\ORM\Mapping as ORM;


trait UserSecurityTrait
{
    #[ORM\Column(length: 180, unique: true, nullable: true)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean', options: ["default" => false])]
    private ?bool $enabled = false;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $securityVersion = 1;

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Si le tableau est vide, retourner ROLE_USER
        if (empty($roles)) {
            return ['ROLE_USER'];
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSecurityVersion(): int
    {
        return $this->securityVersion;
    }

    /**
     * Invalidates every previously authenticated security context for this user.
     */
    public function incrementSecurityVersion(): void
    {
        ++$this->securityVersion;
    }
}
