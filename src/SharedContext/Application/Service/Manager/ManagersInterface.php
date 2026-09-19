<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Manager;

use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * Interface ManagersInterface
 * @package Websymphonie\SharedContext\Application\Service\Manager
 */
interface ManagersInterface
{
    /**
     * Persist les données entité dans la base de données (Entity)
     * Persist les données logs dans la base de données (Logs)
     *
     * @param object $objet
     * @param DbActionEnum $action
     *
     * @return void
     */
    public function execute(object $objet, DbActionEnum $action): void;
}