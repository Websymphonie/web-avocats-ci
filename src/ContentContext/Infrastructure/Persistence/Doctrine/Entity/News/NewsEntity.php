<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\News\NewsRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class NewsEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: 'text')]
    private string $body = '';

    #[ORM\Column(enumType: NewsStatus::class)]
    private NewsStatus $status = NewsStatus::DRAFT;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $coverMediaId = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $photoGalleryId = null;

    /** @var Collection<int, NewsCategoryEntity> */
    #[ORM\ManyToMany(targetEntity: NewsCategoryEntity::class)]
    #[ORM\JoinTable(name: 'news_news_category')]
    private Collection $categories;

    /** @var Collection<int, TagEntity> */
    #[ORM\ManyToMany(targetEntity: TagEntity::class)]
    #[ORM\JoinTable(name: 'news_tag')]
    private Collection $tags;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $excerpt): self { $this->excerpt = $excerpt; return $this; }
    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }
    public function getStatus(): NewsStatus { return $this->status; }
    public function setStatus(NewsStatus $status): self { $this->status = $status; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $publishedAt): self { $this->publishedAt = $publishedAt; return $this; }
    public function getCoverMediaId(): ?int { return $this->coverMediaId; }
    public function setCoverMediaId(?int $value): self { $this->coverMediaId = $value; return $this; }
    public function getPhotoGalleryId(): ?int { return $this->photoGalleryId; }
    public function setPhotoGalleryId(?int $value): self { $this->photoGalleryId = $value; return $this; }
    /** @return Collection<int, NewsCategoryEntity> */
    public function getCategories(): Collection { return $this->categories; }
    /** @param iterable<NewsCategoryEntity> $categories */
    public function replaceCategories(iterable $categories): self { $this->categories->clear(); foreach ($categories as $category) { $this->categories->add($category); } return $this; }
    /** @return Collection<int, TagEntity> */
    public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TagEntity> $tags */
    public function replaceTags(iterable $tags): self { $this->tags->clear(); foreach ($tags as $tag) { $this->tags->add($tag); } return $this; }
}
