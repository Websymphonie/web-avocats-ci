<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidPageException;
use Websymphonie\ContentContext\Domain\Exception\InvalidPageTransitionException;

final class Page
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public string $content,
        public PageStatus $status = PageStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->assertRequiredIdentity($title, $slug);
        $this->title = trim($title);
        $this->slug = trim($slug);
    }

    public function update(string $title, string $slug, string $content): void
    {
        $this->assertRequiredIdentity($title, $slug);
        $this->title = trim($title);
        $this->slug = trim($slug);
        $this->content = $content;
    }

    public function publish(): void
    {
        if ($this->status !== PageStatus::DRAFT) {
            throw new InvalidPageTransitionException('Seul un brouillon peut être publié.');
        }

        $textContent = trim(html_entity_decode(strip_tags($this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (trim($this->title) === '' || trim($this->slug) === '' || $textContent === '') {
            throw new InvalidPageException('Une page publiée doit avoir un titre, un slug et un contenu non vide.');
        }

        $this->status = PageStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        if ($this->status !== PageStatus::PUBLISHED) {
            throw new InvalidPageTransitionException('Seule une page publiée peut être dépubliée.');
        }

        $this->status = PageStatus::DRAFT;
    }

    private function assertRequiredIdentity(string $title, string $slug): void
    {
        if (trim($title) === '') {
            throw new InvalidPageException('Le titre de la page est obligatoire.');
        }

        if (trim($slug) === '') {
            throw new InvalidPageException('Le slug de la page est obligatoire.');
        }
    }
}
