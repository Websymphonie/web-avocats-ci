<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Model\PageListResult;

interface PageRepositoryInterface
{
    public function save(Page $page): Page;
    public function getById(int $id): Page;
    public function delete(Page $page): void;

    /** @param list<Page> $pages */
    public function deleteMany(array $pages): void;
    public function countMediaUsage(int $mediaId): int;
    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * @param list<int> $ids
     * @return list<Page>
     */
    public function findByIds(array $ids): array;

    public function list(?string $search, ?PageStatus $status, int $page, int $limit): PageListResult;
    public function findPublishedBySlug(string $slug): ?Page;

    /** @return list<Page> */
    public function findPublishedByGroup(PageGroup $group): array;
}
