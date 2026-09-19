<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Repository;

use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Model\DocumentPublicationListResult;

interface DocumentPublicationRepositoryInterface
{
    public function save(DocumentPublication $document): DocumentPublication;
    public function getById(int $id): DocumentPublication;
    public function getByUuid(string $uuid): DocumentPublication;
    public function delete(DocumentPublication $document): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    /**
     * @param list<int> $ids
     * @return list<DocumentPublication>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, ?DocumentStatus $status, ?DocumentAccessLevel $accessLevel, ?int $tagId, int $page, int $limit): DocumentPublicationListResult;
    public function countStoredFileUsage(int $storedFileId): int;
}
