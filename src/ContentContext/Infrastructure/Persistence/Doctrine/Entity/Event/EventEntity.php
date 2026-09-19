<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Event\EventRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event_entity')]
#[ORM\HasLifecycleCallbacks]
class EventEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)] private string $title = '';
    #[ORM\Column(length: 255, unique: true)] private string $slug = '';
    #[ORM\Column(type: 'text', nullable: true)] private ?string $excerpt = null;
    #[ORM\Column(type: 'text')] private string $description = '';
    #[ORM\Column(enumType: EventFormat::class)] private EventFormat $format = EventFormat::IN_PERSON;
    #[ORM\Column(type: 'datetime_immutable')] private DateTimeImmutable $startsAt;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?DateTimeImmutable $endsAt = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $venueName = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $address = null;
    #[ORM\Column(length: 2048, nullable: true)] private ?string $onlineUrl = null;
    #[ORM\Column(enumType: EventStatus::class)] private EventStatus $status = EventStatus::DRAFT;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?DateTimeImmutable $publishedAt = null;

    /** @var Collection<int, EventCategoryEntity> */
    #[ORM\ManyToMany(targetEntity: EventCategoryEntity::class)]
    #[ORM\JoinTable(name: 'event_event_category')]
    private Collection $categories;

    /** @var Collection<int, TagEntity> */
    #[ORM\ManyToMany(targetEntity: TagEntity::class)]
    #[ORM\JoinTable(name: 'event_tag')]
    private Collection $tags;

    public function __construct()
    {
        $this->startsAt = new DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): self { $this->slug = $value; return $this; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $value): self { $this->excerpt = $value; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }
    public function getFormat(): EventFormat { return $this->format; }
    public function setFormat(EventFormat $value): self { $this->format = $value; return $this; }
    public function getStartsAt(): DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(DateTimeImmutable $value): self { $this->startsAt = $value; return $this; }
    public function getEndsAt(): ?DateTimeImmutable { return $this->endsAt; }
    public function setEndsAt(?DateTimeImmutable $value): self { $this->endsAt = $value; return $this; }
    public function getVenueName(): ?string { return $this->venueName; }
    public function setVenueName(?string $value): self { $this->venueName = $value; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $value): self { $this->address = $value; return $this; }
    public function getOnlineUrl(): ?string { return $this->onlineUrl; }
    public function setOnlineUrl(?string $value): self { $this->onlineUrl = $value; return $this; }
    public function getStatus(): EventStatus { return $this->status; }
    public function setStatus(EventStatus $value): self { $this->status = $value; return $this; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?DateTimeImmutable $value): self { $this->publishedAt = $value; return $this; }
    /** @return Collection<int, EventCategoryEntity> */
    public function getCategories(): Collection { return $this->categories; }
    /** @param iterable<EventCategoryEntity> $items */
    public function replaceCategories(iterable $items): self { $this->categories->clear(); foreach ($items as $item) { $this->categories->add($item); } return $this; }
    /** @return Collection<int, TagEntity> */
    public function getTags(): Collection { return $this->tags; }
    /** @param iterable<TagEntity> $items */
    public function replaceTags(iterable $items): self { $this->tags->clear(); foreach ($items as $item) { $this->tags->add($item); } return $this; }
}
