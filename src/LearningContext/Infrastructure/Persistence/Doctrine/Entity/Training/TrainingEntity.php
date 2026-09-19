<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Training\TrainingRepository;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[ORM\Table(name: 'training')]
#[ORM\HasLifecycleCallbacks]
class TrainingEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(enumType: TrainingType::class)]
    private TrainingType $type = TrainingType::COURSE;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text')]
    private string $summary = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(enumType: TrainingVisibility::class)]
    private TrainingVisibility $visibility = TrainingVisibility::PUBLIC;

    #[ORM\Column(enumType: TrainingAccessType::class)]
    private TrainingAccessType $accessType = TrainingAccessType::FREE;

    #[ORM\Column(enumType: TrainingStatus::class)]
    private TrainingStatus $status = TrainingStatus::DRAFT;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $coverMediaId = null;

    /** @var Collection<int, TrainingCategoryEntity> */
    #[ORM\ManyToMany(targetEntity: TrainingCategoryEntity::class)]
    #[ORM\JoinTable(name: 'training_training_category')]
    private Collection $categories;

    /** @var Collection<int, TrainingTagEntity> */
    #[ORM\ManyToMany(targetEntity: TrainingTagEntity::class)]
    #[ORM\JoinTable(name: 'training_training_tag')]
    private Collection $tags;

    public function __construct() { $this->categories = new ArrayCollection(); $this->tags = new ArrayCollection(); }

    public function getType(): TrainingType { return $this->type; }
    public function setType(TrainingType $value): self { $this->type = $value; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
    public function getSummary(): string { return $this->summary; }
    public function setSummary(string $value): self { $this->summary = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getVisibility(): TrainingVisibility { return $this->visibility; }
    public function setVisibility(TrainingVisibility $value): self { $this->visibility = $value; return $this; }
    public function getAccessType(): TrainingAccessType { return $this->accessType; }
    public function setAccessType(TrainingAccessType $value): self { $this->accessType = $value; return $this; }
    public function getStatus(): TrainingStatus { return $this->status; }
    public function setStatus(TrainingStatus $value): self { $this->status = $value; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $value): self { $this->publishedAt = $value; return $this; }
    public function getCoverMediaId(): ?int { return $this->coverMediaId; }
    public function setCoverMediaId(?int $value): self { $this->coverMediaId = $value; return $this; }
    /** @return Collection<int, TrainingCategoryEntity> */
    public function getCategories(): Collection { return $this->categories; }
    /** @param iterable<TrainingCategoryEntity> $items */
    public function replaceCategories(iterable $items): self { $this->categories->clear(); foreach ($items as $item) { $this->categories->add($item); } return $this; }
    /** @return Collection<int, TrainingTagEntity> */
    public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TrainingTagEntity> $items */
    public function replaceTags(iterable $items): self { $this->tags->clear(); foreach ($items as $item) { $this->tags->add($item); } return $this; }
}
