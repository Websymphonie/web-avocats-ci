<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity]
#[ORM\Table(name: 'photo_gallery_item')]
#[ORM\UniqueConstraint(name: 'uniq_gallery_media', columns: ['gallery_id', 'media_id'])]
class PhotoGalleryItemEntity
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'items', targetEntity: PhotoGalleryEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PhotoGalleryEntity $gallery = null;
    #[ORM\Column(type: 'integer')] private int $mediaId = 0;
    #[ORM\Column(type: 'integer')] private int $position = 0;
    #[ORM\Column(length: 500)] private string $altText = '';
    #[ORM\Column(type: 'text', nullable: true)] private ?string $caption = null;

    public function getGallery(): ?PhotoGalleryEntity { return $this->gallery; }
    public function setGallery(PhotoGalleryEntity $value): self { $this->gallery = $value; return $this; }
    public function getMediaId(): int { return $this->mediaId; }
    public function setMediaId(int $value): self { $this->mediaId = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $value): self { $this->position = $value; return $this; }
    public function getAltText(): string { return $this->altText; }
    public function setAltText(string $value): self { $this->altText = $value; return $this; }
    public function getCaption(): ?string { return $this->caption; }
    public function setCaption(?string $value): self { $this->caption = $value; return $this; }
}
