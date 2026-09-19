<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

final class PhotoGalleryItem
{
    public function __construct(public readonly int $id, public readonly int $mediaId, public int $position, public string $altText = '', public ?string $caption = null) {}

    public function updateMetadata(string $altText, ?string $caption): void
    {
        $this->altText = trim($altText);
        $caption = $caption !== null ? trim($caption) : null;
        $this->caption = $caption === '' ? null : $caption;
    }
}
