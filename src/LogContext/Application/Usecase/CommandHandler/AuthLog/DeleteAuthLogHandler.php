<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\CommandHandler\AuthLog;

use Websymphonie\LogContext\Application\Usecase\Command\AuthLog\DeleteAuthLogCommand;
use Websymphonie\LogContext\Domain\Repository\AuthLog\AuthLogModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteAuthLogHandler implements CommandHandler
{
    public function __construct(private AuthLogModelRepository $repository)
    {
    }

    public function __invoke(DeleteAuthLogCommand $command): void
    {
        $authLog = $this->repository->getById($command->id);
        $this->repository->remove($authLog);
    }
}