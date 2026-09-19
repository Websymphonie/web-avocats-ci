<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetDocumentPublicationDetailsQueryHandler implements QueryHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository) {}
    public function __invoke(GetDocumentPublicationDetailsQuery $query): DocumentPublication { return $this->repository->getById($query->id); }
}
