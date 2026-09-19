<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\TrainingTag\TrainingTagRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: TrainingTagRepository::class)]
#[ORM\Table(name: 'training_tag')]
#[ORM\HasLifecycleCallbacks]
class TrainingTagEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 150)] private string $name = '';
    #[ORM\Column(length: 180, unique: true)] private string $slug = '';
    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
}
