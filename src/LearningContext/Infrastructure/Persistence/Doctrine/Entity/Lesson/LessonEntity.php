<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
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

    #[ORM\Column(type: 'integer')]
    private int $moduleId = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 1;

    public function getModuleId(): int { return $this->moduleId; }
    public function setModuleId(int $value): self { $this->moduleId = $value; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $value): self { $this->summary = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $value): self { $this->position = $value; return $this; }
}
