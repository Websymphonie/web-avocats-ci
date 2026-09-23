<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Repository;

use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryMember;
use Websymphonie\LawyerContext\Domain\Model\LawyerPublicProfile;

interface LawyerDirectoryRepositoryInterface
{
    public function listPublic(string $name, string $cabinet, string $location, int $page, int $limit): LawyerDirectoryResult;

    public function findPublicProfileByUuid(string $uuid): ?LawyerPublicProfile;

    /** @return list<LawyerDirectoryMember> */
    public function listPublicMembersByCabinetUuid(string $cabinetUuid): array;
}
