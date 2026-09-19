<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidPhotoGalleryException;
use Websymphonie\ContentContext\Domain\Exception\InvalidPhotoGalleryTransitionException;

final class PhotoGallery
{
    /**
     * @param list<Tag> $tags
     * @param list<PhotoGalleryItem> $items
     */
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public string $description = '',
        public PhotoGalleryStatus $status = PhotoGalleryStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?int $coverMediaId = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public array $tags = [],
        public array $items = [],
    ) {}

    /** @param list<Tag> $tags */
    public function update(string $title, string $slug, string $description, array $tags): void
    {
        $this->title = trim($title);
        $this->description = $description;
        if ($this->status === PhotoGalleryStatus::DRAFT) { $this->slug = $slug; }
        $this->tags = $tags;
    }

    /** @param list<int> $mediaIds */
    public function addMedia(array $mediaIds): void
    {
        $known = array_map(static fn (PhotoGalleryItem $item): int => $item->mediaId, $this->items);
        foreach ($mediaIds as $mediaId) {
            if ($mediaId < 1 || in_array($mediaId, $known, true)) { throw new InvalidPhotoGalleryException('Chaque image ne peut être ajoutée qu’une seule fois à une galerie.'); }
            $this->items[] = new PhotoGalleryItem(0, $mediaId, count($this->items) + 1);
            $known[] = $mediaId;
        }
    }

    public function updateItemMetadata(int $mediaId, string $altText, ?string $caption): void
    {
        $item = $this->findItem($mediaId);
        $item->updateMetadata($altText, $caption);
    }

    public function removeItem(int $mediaId): void
    {
        $this->findItem($mediaId);
        $this->items = array_values(array_filter($this->items, static fn (PhotoGalleryItem $item): bool => $item->mediaId !== $mediaId));
        if ($this->coverMediaId === $mediaId) { $this->coverMediaId = null; }
        $this->normalizePositions();
    }

    /** @param list<int> $mediaIds */
    public function reorderItems(array $mediaIds): void
    {
        if (count($mediaIds) !== count($this->items) || count(array_unique($mediaIds)) !== count($mediaIds)) { throw new InvalidPhotoGalleryException('L’ordre transmis est incomplet ou contient des doublons.'); }
        $byId = [];
        foreach ($this->items as $item) { $byId[$item->mediaId] = $item; }
        $ordered = [];
        foreach ($mediaIds as $position => $mediaId) {
            if (!isset($byId[$mediaId])) { throw new InvalidPhotoGalleryException('Une image étrangère ne peut pas être ajoutée à l’ordre de la galerie.'); }
            $byId[$mediaId]->position = $position + 1;
            $ordered[] = $byId[$mediaId];
        }
        $this->items = $ordered;
    }

    public function setCover(int $mediaId): void { $this->findItem($mediaId); $this->coverMediaId = $mediaId; }

    public function publish(): void
    {
        if ($this->status !== PhotoGalleryStatus::DRAFT) { throw new InvalidPhotoGalleryTransitionException('Seule une galerie brouillon peut être publiée.'); }
        if (trim($this->title) === '' || $this->items === []) { throw new InvalidPhotoGalleryException('Une galerie publiée doit avoir un titre et au moins une image.'); }
        if ($this->coverMediaId === null) { throw new InvalidPhotoGalleryException('Choisissez une image de couverture avant de publier la galerie.'); }
        foreach ($this->items as $item) {
            if (trim($item->altText) === '') { throw new InvalidPhotoGalleryException('Chaque image doit avoir un texte alternatif avant publication.'); }
        }
        $this->findItem($this->coverMediaId);
        $this->status = PhotoGalleryStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function archive(): void
    {
        if ($this->status !== PhotoGalleryStatus::PUBLISHED) { throw new InvalidPhotoGalleryTransitionException('Seule une galerie publiée peut être archivée.'); }
        $this->status = PhotoGalleryStatus::ARCHIVED;
    }

    private function findItem(int $mediaId): PhotoGalleryItem
    {
        foreach ($this->items as $item) { if ($item->mediaId === $mediaId) { return $item; } }
        throw new InvalidPhotoGalleryException('Cette image n’appartient pas à la galerie.');
    }

    private function normalizePositions(): void { foreach ($this->items as $index => $item) { $item->position = $index + 1; } }
}
