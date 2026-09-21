<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Repository;

use Websymphonie\MediaContext\Domain\Model\StoredFile;

interface StoredFileRepositoryInterface
{
    public function save(StoredFile $file): StoredFile;
    public function getById(int $id): StoredFile;

    /**
     * @param list<int> $ids
     * @return list<StoredFile>
     */
    public function findByIds(array $ids): array;

    public function delete(StoredFile $file): void;
}
