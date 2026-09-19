<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Model\TagListResult;

interface TagRepositoryInterface
{
    public function save(Tag $tag): Tag;
    public function getById(int $id): Tag;

    /**
     * @param list<int> $ids
     * @return list<Tag>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): TagListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countNewsUsage(int $id): int;
    public function delete(Tag $tag): void;
}
