<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Model\EventCategoryListResult;

interface EventCategoryRepositoryInterface
{
    public function save(EventCategory $category): EventCategory;
    public function getById(int $id): EventCategory;
    /**
     * @param list<int> $ids
     * @return list<EventCategory>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): EventCategoryListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countEventUsage(int $id): int;
    public function delete(EventCategory $category): void;
}
