<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Document;

use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetMemberFundResourcesQuery;
use Websymphonie\ContentContext\Domain\Model\MemberFundResourceList;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberFundResourcesHandler implements QueryHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $documents) {}

    public function __invoke(GetMemberFundResourcesQuery $query): MemberFundResourceList
    {
        return $this->documents->listPublishedLawyerResourcesByTagSlug(
            $query->tagSlug,
            max(1, $query->page),
            max(1, $query->limit),
        );
    }
}
