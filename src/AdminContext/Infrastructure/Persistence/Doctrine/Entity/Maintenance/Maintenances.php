<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Maintenance\MaintenancesRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: MaintenancesRepository::class)]
class Maintenances
{
    use IdTrait;
    use UuidTrait;

    #[ORM\Column(type: 'boolean', options: ["default" => 0])]
    private bool $active = false;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function __toString(): string
    {
        return 'Maintenance du site';
    }
}
