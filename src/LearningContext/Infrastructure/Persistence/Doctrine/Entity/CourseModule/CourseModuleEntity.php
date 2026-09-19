<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\CourseModule\CourseModuleRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: CourseModuleRepository::class)]
#[ORM\Table(name: 'course_module')]
#[ORM\Index(columns: ['training_id'])]
#[ORM\UniqueConstraint(name: 'uniq_course_module_training_position', columns: ['training_id', 'position'])]
#[ORM\HasLifecycleCallbacks]
class CourseModuleEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(type: 'integer')]
    private int $trainingId = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(type: 'integer')]
    private int $position = 1;

    public function getTrainingId(): int { return $this->trainingId; }
    public function setTrainingId(int $value): self { $this->trainingId = $value; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $value): self { $this->position = $value; return $this; }
}
