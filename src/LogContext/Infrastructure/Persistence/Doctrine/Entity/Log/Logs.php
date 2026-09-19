<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\Log\LogsRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: LogsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Logs
{
    use DatesTrait;
    use IdTrait;
    use UuidTrait;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $context = [];

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $level = null;

    #[ORM\Column(length: 50)]
    private ?string $levelName = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $extra = [];

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?User $user = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getContext(): array
    {
        return $this->context;
    }

    /** @param array<string, mixed> $context */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLevelName(): ?string
    {
        return $this->levelName;
    }

    public function setLevelName(string $levelName): static
    {
        $this->levelName = $levelName;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /** @param array<string, mixed> $extra */
    public function setExtra(array $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
