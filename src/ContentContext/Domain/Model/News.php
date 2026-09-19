<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidNewsTransitionException;

final class News
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $body,
        public NewsStatus $status = NewsStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        /** @var list<NewsCategory> */
        public array $categories = [],
        /** @var list<Tag> */
        public array $tags = [],
    ) {
    }

    public function update(string $title, string $slug, ?string $excerpt, string $body): void
    {
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->body = $body;

        if ($this->status === NewsStatus::DRAFT) {
            $this->slug = $slug;
        }
    }

    /** @param list<NewsCategory> $categories */
    public function replaceCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /** @param list<Tag> $tags */
    public function replaceTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function publish(): void
    {
        if ($this->status !== NewsStatus::DRAFT) {
            throw new InvalidNewsTransitionException('Seul un brouillon peut être publié.');
        }

        $this->status = NewsStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function archive(): void
    {
        if ($this->status !== NewsStatus::PUBLISHED) {
            throw new InvalidNewsTransitionException('Seule une actualité publiée peut être archivée.');
        }

        $this->status = NewsStatus::ARCHIVED;
    }
}
