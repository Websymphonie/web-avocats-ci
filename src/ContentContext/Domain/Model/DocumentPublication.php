<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidDocumentPublicationException;
use Websymphonie\ContentContext\Domain\Exception\InvalidDocumentTransitionException;

final class DocumentPublication
{
    /** @param list<Tag> $tags */
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public string $description,
        public readonly int $storedFileId,
        public DocumentAccessLevel $accessLevel = DocumentAccessLevel::PUBLIC,
        public DocumentStatus $status = DocumentStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public array $tags = [],
    ) {}

    /** @param list<Tag> $tags */
    public function update(string $title, string $slug, string $description, DocumentAccessLevel $accessLevel, array $tags): void
    {
        $this->title = trim($title);
        $this->description = $description;
        $this->accessLevel = $accessLevel;
        if ($this->status === DocumentStatus::DRAFT) { $this->slug = $slug; }
        $this->tags = $tags;
    }

    public function publish(): void
    {
        if ($this->status !== DocumentStatus::DRAFT) { throw new InvalidDocumentTransitionException('Seul un document brouillon peut être publié.'); }
        if (trim($this->title) === '' || $this->storedFileId < 1) { throw new InvalidDocumentPublicationException('Un document publié doit avoir un titre et un fichier.'); }
        if (!in_array($this->accessLevel, DocumentAccessLevel::cases(), true)) { throw new InvalidDocumentPublicationException('Le niveau d’accès du document est invalide.'); }
        $this->status = DocumentStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function archive(): void
    {
        if ($this->status !== DocumentStatus::PUBLISHED) { throw new InvalidDocumentTransitionException('Seul un document publié peut être archivé.'); }
        $this->status = DocumentStatus::ARCHIVED;
    }
}
