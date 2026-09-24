<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity]
#[ORM\Table(name: 'page_person_entry')]
#[ORM\UniqueConstraint(name: 'UNIQ_PAGE_PERSON_ENTRY_KEY', columns: ['group_id', 'entry_key'])]
#[ORM\HasLifecycleCallbacks]
class PagePersonEntryEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\ManyToOne(targetEntity: PagePersonGroupEntity::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PagePersonGroupEntity $group;

    #[ORM\Column(name: 'entry_key', length: 120)]
    private string $key = '';

    #[ORM\Column(name: 'display_name', length: 255)]
    private string $displayName = '';

    #[ORM\Column(name: 'role_label', length: 255, nullable: true)]
    private ?string $roleLabel = null;

    #[ORM\Column(name: 'period_label', length: 255, nullable: true)]
    private ?string $periodLabel = null;

    #[ORM\Column(name: 'portrait_media_id', type: 'integer', nullable: true)]
    private ?int $portraitMediaId = null;

    #[ORM\Column(name: 'link_url', length: 2048, nullable: true)]
    private ?string $linkUrl = null;

    #[ORM\Column(name: 'sort_order', type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    public function getGroup(): PagePersonGroupEntity { return $this->group; }
    public function setGroup(PagePersonGroupEntity $group): self { $this->group = $group; return $this; }
    public function getKey(): string { return $this->key; }
    public function setKey(string $key): self { $this->key = trim($key); return $this; }
    public function getDisplayName(): string { return $this->displayName; }
    public function setDisplayName(string $displayName): self { $this->displayName = trim($displayName); return $this; }
    public function getRoleLabel(): ?string { return $this->roleLabel; }
    public function setRoleLabel(?string $roleLabel): self { $this->roleLabel = self::nullableText($roleLabel); return $this; }
    public function getPeriodLabel(): ?string { return $this->periodLabel; }
    public function setPeriodLabel(?string $periodLabel): self { $this->periodLabel = self::nullableText($periodLabel); return $this; }
    public function getPortraitMediaId(): ?int { return $this->portraitMediaId; }
    public function setPortraitMediaId(?int $portraitMediaId): self { $this->portraitMediaId = $portraitMediaId; return $this; }
    public function getLinkUrl(): ?string { return $this->linkUrl; }
    public function setLinkUrl(?string $linkUrl): self { $this->linkUrl = self::nullableText($linkUrl); return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    private static function nullableText(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}
