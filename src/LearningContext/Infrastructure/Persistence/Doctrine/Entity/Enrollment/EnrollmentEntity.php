<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Enrollment\EnrollmentRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\Table(name: 'enrollment')]
#[ORM\UniqueConstraint(name: 'uniq_enrollment_training_user', columns: ['training_id', 'user_id'])]
#[ORM\Index(columns: ['training_id'])]
#[ORM\Index(columns: ['user_id'])]
#[ORM\Index(columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class EnrollmentEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(type: 'integer')]
    private int $trainingId = 0;
    #[ORM\Column(type: 'integer')]
    private int $userId = 0;
    #[ORM\Column(enumType: EnrollmentStatus::class)]
    private EnrollmentStatus $status = EnrollmentStatus::ACTIVE;
    #[ORM\Column(enumType: EnrollmentSource::class)]
    private EnrollmentSource $source = EnrollmentSource::SELF_SERVICE;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $activatedAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    public function getTrainingId(): int { return $this->trainingId; }
    public function setTrainingId(int $value): self { $this->trainingId = $value; return $this; }
    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $value): self { $this->userId = $value; return $this; }
    public function getStatus(): EnrollmentStatus { return $this->status; }
    public function setStatus(EnrollmentStatus $value): self { $this->status = $value; return $this; }
    public function getSource(): EnrollmentSource { return $this->source; }
    public function setSource(EnrollmentSource $value): self { $this->source = $value; return $this; }
    public function getActivatedAt(): ?DateTimeImmutable { return $this->activatedAt; }
    public function setActivatedAt(?DateTimeImmutable $value): self { $this->activatedAt = $value; return $this; }
    public function getRevokedAt(): ?DateTimeImmutable { return $this->revokedAt; }
    public function setRevokedAt(?DateTimeImmutable $value): self { $this->revokedAt = $value; return $this; }
}
