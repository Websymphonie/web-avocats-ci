<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Lesson\LessonRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'lesson')]
#[ORM\Index(columns: ['module_id'])]
#[ORM\UniqueConstraint(name: 'uniq_lesson_module_position', columns: ['module_id', 'position'])]
#[ORM\HasLifecycleCallbacks]
class LessonEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: CourseModuleEntity::class)]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?CourseModuleEntity $module = null;

    #[ORM\Column(name: 'module_id', type: 'integer', insertable: false, updatable: false)]
    private int $moduleId = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $videoProvider = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $externalVideoId = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 1;

    public function getModuleId(): int { return $this->moduleId; }
    public function setModuleId(int $value): self { $this->moduleId = $value; return $this; }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function syncModuleAssociation(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->moduleId > 0 && ($this->module === null || $this->module->getId() !== $this->moduleId)) {
            $this->module = $event->getObjectManager()->getReference(CourseModuleEntity::class, $this->moduleId);
        }
    }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $value): self { $this->summary = $value; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $value): self { $this->content = $value; return $this; }
    public function getVideoProvider(): ?string { return $this->videoProvider; }
    public function setVideoProvider(?string $value): self { $this->videoProvider = $value; return $this; }
    public function getVideoUrl(): ?string { return $this->videoUrl; }
    public function setVideoUrl(?string $value): self { $this->videoUrl = $value; return $this; }
    public function getExternalVideoId(): ?string { return $this->externalVideoId; }
    public function setExternalVideoId(?string $value): self { $this->externalVideoId = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $value): self { $this->position = $value; return $this; }
}
