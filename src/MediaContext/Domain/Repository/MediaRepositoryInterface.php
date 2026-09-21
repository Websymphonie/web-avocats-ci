<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Repository;

use Websymphonie\MediaContext\Domain\Model\Media;

interface MediaRepositoryInterface
{
    public function save(Media $media): Media;

    public function getById(int $id): Media;

    /**
     * @param list<int> $ids
     * @return list<Media>
     */
    public function findByIds(array $ids): array;

    public function delete(Media $media): void;
}
