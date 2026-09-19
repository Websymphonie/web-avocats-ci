<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationListQuery;
use Websymphonie\ContentContext\Domain\Model\DocumentPublicationListResult;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetDocumentPublicationListQueryHandler implements QueryHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository) {}
    public function __invoke(GetDocumentPublicationListQuery $query): DocumentPublicationListResult { return $this->repository->list($query->search, $query->status, $query->accessLevel, $query->tagId, max(1, $query->page), $query->limit); }
}
