<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress;

use DateTimeImmutable;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LessonProgress\LessonProgressRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: LessonProgressRepository::class)]
#[ORM\Table(name: 'lesson_progress')]
#[ORM\UniqueConstraint(name: 'uniq_lesson_progress_enrollment_lesson', columns: ['enrollment_id', 'lesson_id'])]
#[ORM\Index(columns: ['enrollment_id'])]
#[ORM\Index(columns: ['lesson_id'])]
#[ORM\Index(columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class LessonProgressEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: EnrollmentEntity::class)]
    #[ORM\JoinColumn(name: 'enrollment_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?EnrollmentEntity $enrollment = null;

    #[ORM\Column(name: 'enrollment_id', type: 'integer', insertable: false, updatable: false)]
    private int $enrollmentId = 0;

    #[ORM\ManyToOne(targetEntity: LessonEntity::class)]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?LessonEntity $lesson = null;

    #[ORM\Column(name: 'lesson_id', type: 'integer', insertable: false, updatable: false)]
    private int $lessonId = 0;

    #[ORM\Column(enumType: LessonProgressStatus::class, length: 20)]
    private LessonProgressStatus $status = LessonProgressStatus::IN_PROGRESS;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $lastAccessedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    public function getEnrollmentId(): int { return $this->enrollmentId; }
    public function setEnrollmentId(int $value): self { $this->enrollmentId = $value; return $this; }
    public function getLessonId(): int { return $this->lessonId; }
    public function setLessonId(int $value): self { $this->lessonId = $value; return $this; }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function syncAssociations(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->enrollmentId > 0 && ($this->enrollment === null || $this->enrollment->getId() !== $this->enrollmentId)) {
            $this->enrollment = $event->getObjectManager()->getReference(EnrollmentEntity::class, $this->enrollmentId);
        }
        if ($this->lessonId > 0 && ($this->lesson === null || $this->lesson->getId() !== $this->lessonId)) {
            $this->lesson = $event->getObjectManager()->getReference(LessonEntity::class, $this->lessonId);
        }
    }

    public function getStatus(): LessonProgressStatus { return $this->status; }
    public function setStatus(LessonProgressStatus $value): self { $this->status = $value; return $this; }
    public function getStartedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?DateTimeImmutable $value): self { $this->startedAt = $value; return $this; }
    public function getLastAccessedAt(): DateTimeImmutable { return $this->lastAccessedAt; }
    public function setLastAccessedAt(DateTimeImmutable $value): self { $this->lastAccessedAt = $value; return $this; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?DateTimeImmutable $value): self { $this->completedAt = $value; return $this; }
}
