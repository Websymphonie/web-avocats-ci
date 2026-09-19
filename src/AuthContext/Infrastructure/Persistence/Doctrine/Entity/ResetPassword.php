<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Repository\ResetPasswordRepository;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResetPassword
{
    use IdTrait;
    use DatesTrait;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $lastResetPassword = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $lastChangePassword = null;

    #[ORM\Column(length: 32, unique: true)]
    private ?string $selector = null;

    #[ORM\Column(length: 64)]
    private ?string $tokenHash = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $passwordResetRequestedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $passwordResetExpiresAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $consumedAt = null;
    private ?string $legacyResetType = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'resetPassword')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getSelector(): ?string { return $this->selector; }
    public function setSelector(?string $selector): self { $this->selector = $selector; return $this; }
    public function getTokenHash(): ?string { return $this->tokenHash; }
    public function setTokenHash(?string $tokenHash): self { $this->tokenHash = $tokenHash; return $this; }

    public function getPasswordResetRequestedAt(): ?\DateTimeImmutable
    {
        return $this->passwordResetRequestedAt;
    }

    public function setPasswordResetRequestedAt(?DateTimeInterface $passwordResetRequestedAt): self
    {
        $this->passwordResetRequestedAt = $passwordResetRequestedAt === null ? null : \DateTimeImmutable::createFromInterface($passwordResetRequestedAt);
        return $this;
    }

    public function getPasswordResetExpiresAt(): ?\DateTimeImmutable
    {
        return $this->passwordResetExpiresAt;
    }

    public function setPasswordResetExpiresAt(?DateTimeInterface $passwordResetExpiresAt): self
    {
        $this->passwordResetExpiresAt = $passwordResetExpiresAt === null ? null : \DateTimeImmutable::createFromInterface($passwordResetExpiresAt);
        return $this;
    }

    /** @deprecated application handlers must pass the injected Clock value to isUsable(). */
    public function isPasswordResetTokenExpired(?\DateTimeImmutable $now = null): bool
    {
        if ($this->passwordResetExpiresAt === null) {
            return true;
        }

        return ($now ?? new \DateTimeImmutable()) > $this->passwordResetExpiresAt;
    }

    public function getLastResetPassword(): ?DateTime
    {
        return $this->lastResetPassword;
    }

    public function setLastResetPassword(?DateTime $lastResetPassword): ResetPassword
    {
        $this->lastResetPassword = $lastResetPassword;
        return $this;
    }

    public function getLastChangePassword(): ?DateTime
    {
        return $this->lastChangePassword;
    }

    public function setLastChangePassword(?DateTime $lastChangePassword): ResetPassword
    {
        $this->lastChangePassword = $lastChangePassword;
        return $this;
    }

    public function getConsumedAt(): ?\DateTimeImmutable { return $this->consumedAt; }
    public function setConsumedAt(?\DateTimeImmutable $consumedAt): self { $this->consumedAt = $consumedAt; return $this; }
    public function matchesSecret(string $secret): bool { return $this->tokenHash !== null && hash_equals($this->tokenHash, hash('sha256', $secret)); }
    public function isUsable(\DateTimeImmutable $now): bool { return $this->selector !== null && $this->tokenHash !== null && $this->consumedAt === null && $this->passwordResetExpiresAt !== null && $now < $this->passwordResetExpiresAt; }
    public function consume(\DateTimeImmutable $at): void { $this->consumedAt = $at; }

    /** @deprecated reset credentials are now selector + hashed secret. */
    public function getPasswordResetToken(): ?string { return null; }
    /** @deprecated reset credentials are now selector + hashed secret. */
    public function setPasswordResetToken(?string $token): self { return $this; }
    /** @deprecated OTP reset flow has been removed. */
    public function getResetType(): ?string { return $this->legacyResetType ?? 'token_code'; }
    /** @deprecated OTP reset flow has been removed. */
    public function setResetType(?string $type): self { $this->legacyResetType = $type; return $this; }
    /** @deprecated OTP reset flow has been removed. */
    public function getResetCodeOtp(): ?string { return null; }
    /** @deprecated OTP reset flow has been removed. */
    public function setResetCodeOtp(?string $code): self { return $this; }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
