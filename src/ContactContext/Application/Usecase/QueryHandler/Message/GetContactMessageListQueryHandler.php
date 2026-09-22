<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\QueryHandler\Message;

use Websymphonie\ContactContext\Application\Usecase\Query\Message\GetContactMessageListQuery;
use Websymphonie\ContactContext\Domain\Model\ContactMessageListResult;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetContactMessageListQueryHandler implements QueryHandler
{
    public function __construct(private ContactMessageRepositoryInterface $repository)
    {
    }

    public function __invoke(GetContactMessageListQuery $query): ContactMessageListResult
    {
        return $this->repository->list($query->status, max(1, $query->page), max(1, $query->limit));
    }
}
