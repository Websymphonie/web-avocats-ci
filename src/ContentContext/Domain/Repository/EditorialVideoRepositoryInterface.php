<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;

interface EditorialVideoRepositoryInterface
{
    public function save(EditorialVideo $video): EditorialVideo;
    public function getById(int $id): EditorialVideo;
    public function delete(EditorialVideo $video): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    /**
     * @param list<int> $ids
     * @return list<EditorialVideo>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, ?EditorialVideoStatus $status, ?VideoProvider $provider, ?int $tagId, int $page, int $limit): EditorialVideoListResult;
}
