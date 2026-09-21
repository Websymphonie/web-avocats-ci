<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Model\EventListResult;

interface EventRepositoryInterface
{
    public function save(Event $event): Event;
    public function getById(int $id): Event;
    public function delete(Event $event): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    /**
     * @param list<int> $ids
     * @return list<Event>
     */
    public function findByIds(array $ids): array;
    public function countMediaUsage(int $mediaId): int;
    public function countPhotoGalleryUsage(int $galleryId): int;
    public function list(?string $search, ?EventStatus $status, ?EventFormat $format, ?int $categoryId, ?int $tagId, int $page, int $limit): EventListResult;
    public function listPublished(int $page, int $limit, ?int $categoryId = null): EventListResult;
    public function getPublishedBySlug(string $slug): Event;
}
