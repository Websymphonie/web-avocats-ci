<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryListQuery;
use Websymphonie\ContentContext\Domain\Model\PhotoGalleryListResult;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPhotoGalleryListQueryHandler implements QueryHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository) {}
    public function __invoke(GetPhotoGalleryListQuery $query): PhotoGalleryListResult { return $this->repository->list($query->search, $query->status, $query->tagId, $query->page, $query->limit); }
}
