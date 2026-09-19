<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\DocumentPublication\DocumentPublicationRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: DocumentPublicationRepository::class)]
#[ORM\Table(name: 'document_publication')]
#[ORM\HasLifecycleCallbacks]
class DocumentPublicationEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;
    #[ORM\Column(length: 255)] private string $title = '';
    #[ORM\Column(length: 255, unique: true)] private string $slug = '';
    #[ORM\Column(type: 'text')] private string $description = '';
    #[ORM\Column(type: 'integer')] private int $storedFileId = 0;
    #[ORM\Column(enumType: DocumentAccessLevel::class)] private DocumentAccessLevel $accessLevel = DocumentAccessLevel::PUBLIC;
    #[ORM\Column(enumType: DocumentStatus::class)] private DocumentStatus $status = DocumentStatus::DRAFT;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?DateTimeImmutable $publishedAt = null;
    /** @var Collection<int, TagEntity> */
    #[ORM\ManyToMany(targetEntity: TagEntity::class)]
    #[ORM\JoinTable(name: 'document_publication_tag')]
    private Collection $tags;
    public function __construct() { $this->tags = new ArrayCollection(); }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getStoredFileId(): int { return $this->storedFileId; }
    public function setStoredFileId(int $value): self { $this->storedFileId = $value; return $this; }
    public function getAccessLevel(): DocumentAccessLevel { return $this->accessLevel; }
    public function setAccessLevel(DocumentAccessLevel $value): self { $this->accessLevel = $value; return $this; }
    public function getStatus(): DocumentStatus { return $this->status; }
    public function setStatus(DocumentStatus $value): self { $this->status = $value; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $value): self { $this->publishedAt = $value; return $this; }
    /** @return Collection<int, TagEntity> */ public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TagEntity> $tags */ public function replaceTags(iterable $tags): self { $this->tags->clear(); foreach ($tags as $tag) { $this->tags->add($tag); } return $this; }
}
