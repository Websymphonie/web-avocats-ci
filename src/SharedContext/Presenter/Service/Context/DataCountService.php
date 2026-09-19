<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Context;

use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\LogContext\Domain\Repository\AuthLog\AuthLogModelRepository;
use Websymphonie\LogContext\Domain\Repository\Log\LogModelRepository;

readonly class DataCountService
{

    public function __construct(
        private UserModelRepositoryInterface $userModelRepository,
        private AuthLogModelRepository       $authLogModelRepository,
        private LogModelRepository           $logModelRepository,
    )
    {

    }

    public function countUsers(): int
    {
        return $this->userModelRepository->countAll();
    }

    public function countAuthLogs(): int
    {
        return $this->authLogModelRepository->count();
    }

    public function countLogs(): int
    {
        return $this->logModelRepository->count();
    }

    public function countUsersLocked(): int
    {
        return $this->userModelRepository->countAll(onlyDisabled: true);
    }
}