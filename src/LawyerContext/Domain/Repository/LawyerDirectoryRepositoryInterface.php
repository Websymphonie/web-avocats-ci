<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Repository;

use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;

interface LawyerDirectoryRepositoryInterface
{
    public function listPublic(string $name, string $cabinet, string $location, int $page, int $limit): LawyerDirectoryResult;
}
