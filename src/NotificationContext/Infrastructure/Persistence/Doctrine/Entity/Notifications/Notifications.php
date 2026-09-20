<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\AddNotificationCommand;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Repository\Notifications\NotificationsRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Notifications
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $message = null;

    #[ORM\Column(enumType: NotificationTypeEnum::class, options: ['default' => NotificationTypeEnum::NOTIF_INFO])]
    private NotificationTypeEnum $type = NotificationTypeEnum::NOTIF_INFO;

    #[ORM\Column(enumType: NotificationAccessEnum::class, options: ['default' => NotificationAccessEnum::NOTIF_PUBLIC])]
    private NotificationAccessEnum $access = NotificationAccessEnum::NOTIF_PUBLIC;

    #[ORM\Column(enumType: NotificationActionEnum::class, options: ['default' => NotificationActionEnum::NOTIF_ADD])]
    private NotificationActionEnum $action = NotificationActionEnum::NOTIF_ADD;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $context = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    #[ORM\Column(name: 'deduplication_key', type: 'string', length: 64, nullable: true, unique: true)]
    private ?string $deduplicationKey = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
    }

    public function add(AddNotificationCommand $command): self
    {
        return $this
            ->setType($command->type)
            ->setMessage($command->message)
            ->setReadAt($command->readAt)
            ->setContext($command->context)
            ->setAction($command->action)
            ->setAccess($command->access)
            ->setDeduplicationKey($command->deduplicationKey);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): Notifications
    {
        $this->title = $title;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getType(): NotificationTypeEnum
    {
        return $this->type;
    }

    public function setType(NotificationTypeEnum $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getAccess(): NotificationAccessEnum
    {
        return $this->access;
    }

    public function setAccess(NotificationAccessEnum $access): Notifications
    {
        $this->access = $access;
        return $this;
    }

    public function getAction(): NotificationActionEnum
    {
        return $this->action;
    }

    public function setAction(NotificationActionEnum $action): Notifications
    {
        $this->action = $action;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getContext(): array
    {
        return $this->context;
    }

    /** @param array<string, mixed> $context */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?DateTimeImmutable $readAt): Notifications
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function getDeduplicationKey(): ?string
    {
        return $this->deduplicationKey;
    }

    public function setDeduplicationKey(?string $deduplicationKey): self
    {
        if ($deduplicationKey !== null && (strlen($deduplicationKey) !== 64 || !ctype_xdigit($deduplicationKey))) {
            throw new InvalidArgumentException('La clé de déduplication doit être un hash SHA-256 hexadécimal.');
        }

        $this->deduplicationKey = $deduplicationKey;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Notifications
    {
        $this->user = $user;
        return $this;
    }

    public function __toString(): string
    {
        return $this->message;
    }

    public function markAsRead(): void
    {
        $this->readAt = new DateTimeImmutable();
    }
}
