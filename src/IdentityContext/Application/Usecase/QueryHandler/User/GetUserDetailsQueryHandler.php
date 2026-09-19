<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\QueryHandler\User;


use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserDetailsQuery;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\IdentityContext\Presenter\ViewModel\User\UserDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetUserDetailsQueryHandler implements QueryHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private UserFactory                  $factory,
    )
    {
    }

    public function __invoke(GetUserDetailsQuery $query): UserDetailViewModel
    {
        $userModel = $this->factory->fromEntity($this->repository->getById($query->userId));
        return new UserDetailViewModel($userModel);
    }
}