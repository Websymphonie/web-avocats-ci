<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\EventCategory\EventCategoryRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: EventCategoryRepository::class)]
#[ORM\Table(name: 'event_category')]
#[ORM\HasLifecycleCallbacks]
class EventCategoryEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(length: 180, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
}
