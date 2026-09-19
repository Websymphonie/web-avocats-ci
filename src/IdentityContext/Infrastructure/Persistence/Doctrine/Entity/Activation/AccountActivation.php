<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Activation\AccountActivationRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity(repositoryClass: AccountActivationRepository::class)]
#[ORM\Table(name: 'account_activations')]
#[ORM\HasLifecycleCallbacks]
final class AccountActivation
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 32, unique: true)]
    private string $selector;

    #[ORM\Column(length: 64)]
    private string $tokenHash;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(User $user, string $selector, string $tokenHash, DateTimeImmutable $expiresAt, DateTimeImmutable $createdAt)
    {
        $this->user = $user;
        $this->selector = $selector;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
    }

    public function getUser(): User { return $this->user; }
    public function getSelector(): string { return $this->selector; }
    public function getExpiresAt(): DateTimeImmutable { return $this->expiresAt; }
    public function getConsumedAt(): ?DateTimeImmutable { return $this->consumedAt; }

    public function matchesToken(string $token): bool
    {
        return hash_equals($this->tokenHash, hash('sha256', $token));
    }

    public function isUsable(DateTimeImmutable $now): bool
    {
        return $this->consumedAt === null && $now < $this->expiresAt;
    }

    public function consume(DateTimeImmutable $at): void
    {
        $this->consumedAt = $at;
    }
}
