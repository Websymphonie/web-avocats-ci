<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\CommandHandler\Reglage;

use Websymphonie\AdminContext\Application\Usecase\Command\Reglage\UpdateReglageCommand;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

readonly class UpdateReglageCommandHandler implements CommandHandler
{
    public function __construct(private ReglageModelRepository $repository)
    {
    }

    public function __invoke(UpdateReglageCommand $command): Reglages
    {
        $reglage = $this->repository->getById($command->id);

        $reglage->setValue($command->value);

        return $this->repository->update($reglage);
    }
}