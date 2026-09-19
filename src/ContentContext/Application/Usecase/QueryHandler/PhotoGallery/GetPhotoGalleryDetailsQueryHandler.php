<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\PhotoGallery;

use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPhotoGalleryDetailsQueryHandler implements QueryHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository) {}
    public function __invoke(GetPhotoGalleryDetailsQuery $query): PhotoGallery { return $this->repository->getById($query->id); }
}
