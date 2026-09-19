<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\CommandHandler\Maintenance;


use Websymphonie\AdminContext\Application\Usecase\Command\Maintenance\UpdateMaintenanceCommand;
use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Domain\Repository\Maintenance\MaintenanceModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateMaintenanceHandler implements CommandHandler
{
    public function __construct(private MaintenanceModelRepository $repository)
    {
    }

    public function __invoke(UpdateMaintenanceCommand $command): MaintenanceModel
    {
        $maintenance = $this->repository->getByEntityId($command->id);
        $maintenance->setActive($command->active);
        return $this->repository->update($maintenance);
    }
}