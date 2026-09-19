<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Model\PhotoGalleryListResult;

interface PhotoGalleryRepositoryInterface
{
    public function save(PhotoGallery $gallery): PhotoGallery;
    public function getById(int $id): PhotoGallery;
    public function delete(PhotoGallery $gallery): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    /**
     * @param list<int> $ids
     * @return list<PhotoGallery>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, ?PhotoGalleryStatus $status, ?int $tagId, int $page, int $limit): PhotoGalleryListResult;
    public function countMediaUsage(int $mediaId): int;
}
