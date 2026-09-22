<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\QueryHandler\Message;

use Websymphonie\ContactContext\Application\Usecase\Query\Message\GetContactMessageDetailsQuery;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetContactMessageDetailsQueryHandler implements QueryHandler
{
    public function __construct(private ContactMessageRepositoryInterface $repository)
    {
    }

    public function __invoke(GetContactMessageDetailsQuery $query): ContactMessage
    {
        return $this->repository->getByUuid($query->uuid);
    }
}
