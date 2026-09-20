<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuthLog\AuthLogRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: AuthLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AuthLog
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;
    use SoftDeleteableEntity;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable $authAttemptAt;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $userIP;

    #[ORM\Column(type: 'string', length: 255)]
    private string $emailEntered;

    #[ORM\Column(type: 'boolean', options: ["default" => false])]
    private bool $isSuccessFulAuth;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $startOfBlackListing = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endOfBlackListing = null;

    #[ORM\Column(type: 'boolean', options: ["default" => false])]
    private bool $isRememberMeAuth;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deauthenticatedAt;

    public function __construct(string $emailEntered, ?string $userIP)
    {
        $this->uuid = Uuid::v7();
        $this->authAttemptAt = new DateTimeImmutable('now');
        $this->emailEntered = $emailEntered;
        $this->isRememberMeAuth = false;
        $this->userIP = $userIP;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getAuthAttemptAt(): DateTimeImmutable
    {
        return $this->authAttemptAt;
    }

    /**
     * @param DateTimeImmutable $authAttemptAt
     * @return AuthLog
     */
    public function setAuthAttemptAt(DateTimeImmutable $authAttemptAt): AuthLog
    {
        $this->authAttemptAt = $authAttemptAt;
        return $this;
    }

    public function getUserIP(): ?string
    {
        return $this->userIP;
    }

    public function setUserIP(?string $userIP): self
    {
        $this->userIP = $userIP;
        return $this;
    }

    public function getEmailEntered(): ?string
    {
        return $this->emailEntered;
    }

    public function setEmailEntered(string $emailEntered): self
    {
        $this->emailEntered = $emailEntered;

        return $this;
    }

    public function getIsSuccessFulAuth(): ?bool
    {
        return $this->isSuccessFulAuth;
    }

    public function setIsSuccessFulAuth(bool $isSuccessFulAuth): self
    {
        $this->isSuccessFulAuth = $isSuccessFulAuth;

        return $this;
    }

    public function getStartOfBlackListing(): ?DateTimeImmutable
    {
        return $this->startOfBlackListing;
    }

    public function setStartOfBlackListing(?DateTimeImmutable $startOfBlackListing): self
    {
        $this->startOfBlackListing = $startOfBlackListing;

        return $this;
    }

    public function getEndOfBlackListing(): ?DateTimeImmutable
    {
        return $this->endOfBlackListing;
    }

    public function setEndOfBlackListing(?DateTimeImmutable $endOfBlackListing): self
    {
        $this->endOfBlackListing = $endOfBlackListing;

        return $this;
    }

    public function getIsRememberMeAuth(): ?bool
    {
        return $this->isRememberMeAuth;
    }

    public function setIsRememberMeAuth(bool $isRememberMeAuth): self
    {
        $this->isRememberMeAuth = $isRememberMeAuth;

        return $this;
    }

    public function getDeauthenticatedAt(): ?DateTimeImmutable
    {
        return $this->deauthenticatedAt;
    }

    public function setDeauthenticatedAt(?DateTimeImmutable $deauthenticatedAt): self
    {
        $this->deauthenticatedAt = $deauthenticatedAt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->emailEntered;
    }
}
