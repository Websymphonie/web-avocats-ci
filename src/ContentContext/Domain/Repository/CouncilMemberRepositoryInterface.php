<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\CouncilMember;

interface CouncilMemberRepositoryInterface
{
    public function save(CouncilMember $member): CouncilMember;
    public function getById(int $id): CouncilMember;

    /** @return list<CouncilMember> */
    public function list(): array;

    /** @return list<CouncilMember> */
    public function listCurrent(): array;

    public function countMediaUsage(int $mediaId): int;
}
