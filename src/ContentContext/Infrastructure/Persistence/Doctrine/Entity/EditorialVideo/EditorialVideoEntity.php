<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideoCategory\EditorialVideoCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\EditorialVideo\EditorialVideoRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: EditorialVideoRepository::class)]
#[ORM\Table(name: 'editorial_video_entity')]
#[ORM\HasLifecycleCallbacks]
class EditorialVideoEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)] private string $title = '';
    #[ORM\Column(length: 255, unique: true)] private string $slug = '';
    #[ORM\Column(type: 'text', nullable: true)] private ?string $excerpt = null;
    #[ORM\Column(type: 'text')] private string $description = '';
    #[ORM\Column(enumType: VideoProvider::class)] private VideoProvider $provider = VideoProvider::YOUTUBE;
    #[ORM\Column(length: 2048)] private string $videoUrl = '';
    #[ORM\Column(length: 255, nullable: true)] private ?string $externalVideoId = null;
    #[ORM\Column(enumType: EditorialVideoStatus::class)] private EditorialVideoStatus $status = EditorialVideoStatus::DRAFT;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?DateTimeImmutable $publishedAt = null;
    #[ORM\ManyToOne(targetEntity: EditorialVideoCategoryEntity::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'RESTRICT')]
    private ?EditorialVideoCategoryEntity $category = null;

    /** @var Collection<int, TagEntity> */
    #[ORM\ManyToMany(targetEntity: TagEntity::class)]
    #[ORM\JoinTable(name: 'editorial_video_tag')]
    private Collection $tags;

    public function __construct() { $this->tags = new ArrayCollection(); }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $value): self { $this->excerpt = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getProvider(): VideoProvider { return $this->provider; }
    public function setProvider(VideoProvider $value): self { $this->provider = $value; return $this; }
    public function getVideoUrl(): string { return $this->videoUrl; }
    public function setVideoUrl(string $value): self { $this->videoUrl = $value; return $this; }
    public function getExternalVideoId(): ?string { return $this->externalVideoId; }
    public function setExternalVideoId(?string $value): self { $this->externalVideoId = $value; return $this; }
    public function getStatus(): EditorialVideoStatus { return $this->status; }
    public function setStatus(EditorialVideoStatus $value): self { $this->status = $value; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $value): self { $this->publishedAt = $value; return $this; }
    public function getCategory(): ?EditorialVideoCategoryEntity { return $this->category; }
    public function setCategory(?EditorialVideoCategoryEntity $value): self { $this->category = $value; return $this; }
    /** @return Collection<int, TagEntity> */
    public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TagEntity> $items */
    public function replaceTags(iterable $items): self { $this->tags->clear(); foreach ($items as $item) { $this->tags->add($item); } return $this; }
}
