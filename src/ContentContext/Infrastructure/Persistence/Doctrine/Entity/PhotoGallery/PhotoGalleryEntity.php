<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\PhotoGallery\PhotoGalleryRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: PhotoGalleryRepository::class)]
#[ORM\Table(name: 'photo_gallery')]
#[ORM\HasLifecycleCallbacks]
class PhotoGalleryEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)] private string $title = '';
    #[ORM\Column(length: 255, unique: true)] private string $slug = '';
    #[ORM\Column(type: 'text')] private string $description = '';
    #[ORM\Column(enumType: PhotoGalleryStatus::class)] private PhotoGalleryStatus $status = PhotoGalleryStatus::DRAFT;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?DateTimeImmutable $publishedAt = null;
    #[ORM\Column(type: 'integer', nullable: true)] private ?int $coverMediaId = null;

    /** @var Collection<int, PhotoGalleryItemEntity> */
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: PhotoGalleryItemEntity::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $items;

    /** @var Collection<int, TagEntity> */
    #[ORM\ManyToMany(targetEntity: TagEntity::class)]
    #[ORM\JoinTable(name: 'photo_gallery_tag')]
    private Collection $tags;

    public function __construct() { $this->items = new ArrayCollection(); $this->tags = new ArrayCollection(); }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getStatus(): PhotoGalleryStatus { return $this->status; }
    public function setStatus(PhotoGalleryStatus $value): self { $this->status = $value; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $value): self { $this->publishedAt = $value; return $this; }
    public function getCoverMediaId(): ?int { return $this->coverMediaId; }
    public function setCoverMediaId(?int $value): self { $this->coverMediaId = $value; return $this; }
    /** @return Collection<int, PhotoGalleryItemEntity> */
    public function getItems(): Collection { return $this->items; }
    /** @param iterable<PhotoGalleryItemEntity> $items */
    public function replaceItems(iterable $items): self
    {
        foreach ($this->items->toArray() as $item) { $this->items->removeElement($item); }
        foreach ($items as $item) { $item->setGallery($this); $this->items->add($item); }
        return $this;
    }
    /** @return Collection<int, TagEntity> */
    public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TagEntity> $tags */
    public function replaceTags(iterable $tags): self { $this->tags->clear(); foreach ($tags as $tag) { $this->tags->add($tag); } return $this; }
}
