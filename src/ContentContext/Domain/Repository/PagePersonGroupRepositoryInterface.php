<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;

interface PagePersonGroupRepositoryInterface
{
    /** @return list<PagePersonGroup> */
    public function listByPageId(int $pageId): array;

    /** @param list<PagePersonGroup> $groups */
    public function synchronizeForPage(int $pageId, array $groups): void;

    public function countMediaUsage(int $mediaId): int;
}
