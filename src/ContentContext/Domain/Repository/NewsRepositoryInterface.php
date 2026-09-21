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
    public function countMediaUsage(int $mediaId): int;
    public function countPhotoGalleryUsage(int $galleryId): int;

    public function list(?string $search, ?NewsStatus $status, int $page, int $limit, ?int $categoryId = null, ?int $tagId = null): NewsListResult;

    public function listPublished(int $page, int $limit, ?int $categoryId = null, ?int $tagId = null): NewsListResult;

    /**
     * @return list<News>
     */
    public function searchPublished(string $term, int $limit): array;

    public function getPublishedBySlug(string $slug): News;
}
