<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;


trait DatesTrait
{

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $createdAt = null;


    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /*public function setCreatedAt(?DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }*/

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (
            property_exists($this, 'isImported')
            && $this->isImported === true
        ) {
            // 👉 Import legacy → on respecte les dates existantes
            return;
        }
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        if (
            property_exists($this, 'isImported')
            && $this->isImported === true
        ) {
            return;
        }
        $this->updatedAt = new DateTimeImmutable();
    }
}
