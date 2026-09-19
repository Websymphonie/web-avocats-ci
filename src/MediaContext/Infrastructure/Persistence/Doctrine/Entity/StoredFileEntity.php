<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Repository\StoredFileRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: StoredFileRepository::class)]
#[ORM\Table(name: 'stored_file')]
#[ORM\HasLifecycleCallbacks]
class StoredFileEntity
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;

    #[ORM\Column(length: 255)] private string $originalName = '';
    #[ORM\Column(length: 255, unique: true)] private string $storageName = '';
    #[ORM\Column(length: 128)] private string $mimeType = '';
    #[ORM\Column(type: 'integer')] private int $size = 0;
    #[ORM\Column(length: 64)] private string $checksum = '';

    public function getOriginalName(): string { return $this->originalName; }
    public function setOriginalName(string $value): self { $this->originalName = $value; return $this; }
    public function getStorageName(): string { return $this->storageName; }
    public function setStorageName(string $value): self { $this->storageName = $value; return $this; }
    public function getMimeType(): string { return $this->mimeType; }
    public function setMimeType(string $value): self { $this->mimeType = $value; return $this; }
    public function getSize(): int { return $this->size; }
    public function setSize(int $value): self { $this->size = $value; return $this; }
    public function getChecksum(): string { return $this->checksum; }
    public function setChecksum(string $value): self { $this->checksum = $value; return $this; }
}
