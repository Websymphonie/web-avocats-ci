<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Page\PageRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'page')]
#[ORM\UniqueConstraint(name: 'UNIQ_PAGE_GROUP_SLUG', columns: ['editorial_group', 'slug'])]
#[ORM\HasLifecycleCallbacks]
class PageEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255)]
    private string $slug = '';

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column(enumType: PageStatus::class)]
    private PageStatus $status = PageStatus::DRAFT;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $coverMediaId = null;

    #[ORM\Column(name: 'editorial_group', nullable: true, enumType: PageGroup::class)]
    private ?PageGroup $editorialGroup = null;

    #[ORM\Column(name: 'sort_order', type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): self
    {
        $this->title = $value;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $value): self
    {
        $this->slug = $value;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $value): self
    {
        $this->content = $value;
        return $this;
    }

    public function getStatus(): PageStatus
    {
        return $this->status;
    }

    public function setStatus(PageStatus $value): self
    {
        $this->status = $value;
        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $value): self
    {
        $this->publishedAt = $value;
        return $this;
    }

    public function getCoverMediaId(): ?int
    {
        return $this->coverMediaId;
    }

    public function setCoverMediaId(?int $value): self
    {
        $this->coverMediaId = $value;
        return $this;
    }

    public function getGroup(): ?PageGroup
    {
        return $this->editorialGroup;
    }

    public function setGroup(?PageGroup $value): self
    {
        $this->editorialGroup = $value;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }
}
