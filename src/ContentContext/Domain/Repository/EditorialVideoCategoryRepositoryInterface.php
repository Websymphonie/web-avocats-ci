<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategoryListResult;

interface EditorialVideoCategoryRepositoryInterface
{
    public function save(EditorialVideoCategory $category): EditorialVideoCategory;
    public function getById(int $id): EditorialVideoCategory;
    /**
     * @param list<int> $ids
     * @return list<EditorialVideoCategory>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): EditorialVideoCategoryListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countVideoUsage(int $id): int;
    public function delete(EditorialVideoCategory $category): void;
}
