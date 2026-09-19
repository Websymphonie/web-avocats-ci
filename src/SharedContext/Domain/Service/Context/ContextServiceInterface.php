<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Context;

use DateTimeImmutable;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;

interface ContextServiceInterface
{
    public function getImage(string $name): ImageModel;

    /** @return list<ReglageModel> */
    public function findAll(): array;

    public function getValue(string $name): mixed;

    public function findMaintenance(): ?MaintenanceModel;

    public function getPaginatorPageSize(): int;

    public function clearCache(): void;

    public function findCurrentYear(): int;

    public function findCurrentDay(): DateTimeImmutable;

    public function monthNumberToName(int $month): string;

    /** @return list<string> */
    public function getMonths(): array;
}
