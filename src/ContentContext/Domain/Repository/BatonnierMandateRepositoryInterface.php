<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;

interface BatonnierMandateRepositoryInterface
{
    public function save(BatonnierMandate $mandate): BatonnierMandate;
    public function getById(int $id): BatonnierMandate;
    public function findCurrent(): ?BatonnierMandate;

    /** @return list<BatonnierMandate> */
    public function list(): array;

    public function countMediaUsage(int $mediaId): int;
}
