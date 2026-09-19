<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Model\NewsListResult;

interface NewsRepositoryInterface
{
    public function save(News $news): News;
    public function getById(int $id): News;
    public function delete(News $news): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * @param list<int> $ids
     * @return list<News>
     */
    public function findByIds(array $ids): array;

    public function list(?string $search, ?NewsStatus $status, int $page, int $limit): NewsListResult;
}
