<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\NewsCategoryListResult;

interface NewsCategoryRepositoryInterface
{
    public function save(NewsCategory $category): NewsCategory;
    public function getById(int $id): NewsCategory;

    /**
     * @param list<int> $ids
     * @return list<NewsCategory>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): NewsCategoryListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countNewsUsage(int $id): int;
    public function delete(NewsCategory $category): void;
}
