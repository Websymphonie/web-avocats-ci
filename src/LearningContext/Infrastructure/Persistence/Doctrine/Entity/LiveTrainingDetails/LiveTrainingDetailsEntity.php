<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LiveTrainingDetails\LiveTrainingDetailsRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: LiveTrainingDetailsRepository::class)]
#[ORM\Table(name: 'live_training_details')]
#[ORM\UniqueConstraint(name: 'uniq_live_training_details_training', columns: ['training_id'])]
#[ORM\HasLifecycleCallbacks]
class LiveTrainingDetailsEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: TrainingEntity::class)]
    #[ORM\JoinColumn(name: 'training_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?TrainingEntity $training = null;

    #[ORM\Column(name: 'training_id', type: 'integer', insertable: false, updatable: false)]
    private int $trainingId = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startsAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endsAt;

    #[ORM\Column(enumType: LiveDeliveryMode::class)]
    private LiveDeliveryMode $deliveryMode = LiveDeliveryMode::ONLINE;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    private ?string $joinUrl = null;

    #[ORM\Column(enumType: VideoProvider::class, nullable: true)]
    private ?VideoProvider $streamProvider = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $externalStreamId = null;

    #[ORM\Column(enumType: VideoProvider::class, nullable: true)]
    private ?VideoProvider $replayProvider = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $replayExternalId = null;

    public function getTrainingId(): int { return $this->trainingId; }
    public function setTrainingId(int $value): self { $this->trainingId = $value; return $this; }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function syncTrainingAssociation(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->trainingId > 0 && ($this->training === null || $this->training->getId() !== $this->trainingId)) {
            $this->training = $event->getObjectManager()->getReference(TrainingEntity::class, $this->trainingId);
        }
    }
    public function getStartsAt(): DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(DateTimeImmutable $value): self { $this->startsAt = $value; return $this; }
    public function getEndsAt(): DateTimeImmutable { return $this->endsAt; }
    public function setEndsAt(DateTimeImmutable $value): self { $this->endsAt = $value; return $this; }
    public function getDeliveryMode(): LiveDeliveryMode { return $this->deliveryMode; }
    public function setDeliveryMode(LiveDeliveryMode $value): self { $this->deliveryMode = $value; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $value): self { $this->location = $value; return $this; }
    public function getJoinUrl(): ?string { return $this->joinUrl; }
    public function setJoinUrl(?string $value): self { $this->joinUrl = $value; return $this; }
    public function getStreamProvider(): ?VideoProvider { return $this->streamProvider; }
    public function setStreamProvider(?VideoProvider $value): self { $this->streamProvider = $value; return $this; }
    public function getExternalStreamId(): ?string { return $this->externalStreamId; }
    public function setExternalStreamId(?string $value): self { $this->externalStreamId = $value; return $this; }
    public function getReplayProvider(): ?VideoProvider { return $this->replayProvider; }
    public function setReplayProvider(?VideoProvider $value): self { $this->replayProvider = $value; return $this; }
    public function getReplayExternalId(): ?string { return $this->replayExternalId; }
    public function setReplayExternalId(?string $value): self { $this->replayExternalId = $value; return $this; }
}
