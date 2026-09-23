<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Repository;

use Websymphonie\LawyerContext\Domain\Model\CabinetPublicProfile;

interface PublicCabinetDirectoryRepositoryInterface
{
    public function findPublicByUuid(string $uuid): ?CabinetPublicProfile;
}
