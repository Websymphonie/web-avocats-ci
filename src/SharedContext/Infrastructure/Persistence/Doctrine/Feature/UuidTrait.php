<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

trait UuidTrait
{
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: true)]
    private ?Uuid $uuid = null;

    #[ORM\PrePersist]
    public function initializeUuid(): void
    {
        if ($this->uuid === null) {
            $this->uuid = Uuid::v7();
        }
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getUuidAsString(): ?string
    {
        return $this->uuid?->toRfc4122();
    }
}
