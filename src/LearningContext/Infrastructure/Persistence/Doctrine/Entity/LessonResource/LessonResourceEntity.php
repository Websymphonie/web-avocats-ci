<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonResource;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LessonResource\LessonResourceRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: LessonResourceRepository::class)]
#[ORM\Table(name: 'lesson_resource')]
#[ORM\Index(columns: ['lesson_id'])]
#[ORM\Index(columns: ['stored_file_id'])]
#[ORM\UniqueConstraint(name: 'uniq_lesson_resource_lesson_position', columns: ['lesson_id', 'position'])]
#[ORM\HasLifecycleCallbacks]
class LessonResourceEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: LessonEntity::class)]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?LessonEntity $lesson = null;

    #[ORM\Column(name: 'lesson_id', type: 'integer', insertable: false, updatable: false)]
    private int $lessonId = 0;
    #[ORM\Column(type: 'integer')]
    private int $storedFileId = 0;
    #[ORM\Column(length: 255)]
    private string $title = '';
    #[ORM\Column(type: 'integer')]
    private int $position = 1;

    public function getLessonId(): int { return $this->lessonId; }
    public function setLessonId(int $value): self { $this->lessonId = $value; return $this; }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function syncLessonAssociation(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->lessonId > 0 && ($this->lesson === null || $this->lesson->getId() !== $this->lessonId)) {
            $this->lesson = $event->getObjectManager()->getReference(LessonEntity::class, $this->lessonId);
        }
    }
    public function getStoredFileId(): int { return $this->storedFileId; }
    public function setStoredFileId(int $value): self { $this->storedFileId = $value; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $value): self { $this->position = $value; return $this; }
}
