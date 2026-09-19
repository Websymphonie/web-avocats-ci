<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\CommandHandler\Log;

use Websymphonie\LogContext\Application\Usecase\Command\Log\DeleteLogCommand;
use Websymphonie\LogContext\Domain\Repository\Log\LogModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteLogHandler implements CommandHandler
{
    public function __construct(private LogModelRepository $repository)
    {
    }

    public function __invoke(DeleteLogCommand $command): void
    {
        $log = $this->repository->getById($command->id);
        $this->repository->remove($log);
    }
}