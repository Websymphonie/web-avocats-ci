<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity]
#[ORM\Table(name: 'page_person_group')]
#[ORM\UniqueConstraint(name: 'UNIQ_PAGE_PERSON_GROUP_KEY', columns: ['page_id', 'group_key'])]
#[ORM\HasLifecycleCallbacks]
class PagePersonGroupEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: PageEntity::class, inversedBy: 'personGroups')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PageEntity $page;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(name: 'group_key', length: 100)]
    private string $key = '';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    /** @var Collection<int, PagePersonEntryEntity> */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: PagePersonEntryEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getPage(): PageEntity { return $this->page; }
    public function setPage(PageEntity $page): self { $this->page = $page; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = trim($title); return $this; }
    public function getKey(): string { return $this->key; }
    public function setKey(string $key): self { $this->key = trim($key); return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    /** @return Collection<int, PagePersonEntryEntity> */
    public function getEntries(): Collection { return $this->entries; }

    public function addEntry(PagePersonEntryEntity $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setGroup($this);
        }

        return $this;
    }

    public function removeEntry(PagePersonEntryEntity $entry): self
    {
        $this->entries->removeElement($entry);

        return $this;
    }
}
