<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Repository\Reglage;

use Doctrine\ORM\Query;
use Websymphonie\AdminContext\Application\Usecase\Command\Reglage\UpdateReglageCommand;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglagePaginateListQuery;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\ReglageListQuery;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;

interface ReglageModelRepository
{
    public function create(Reglages $entity): Reglages;

    public function update(Reglages $entity): Reglages;

    public function remove(Reglages $entity): void;

    public function getById(int $id): Reglages;

    /**
     * @param ReglageListQuery $query
     * @return list<Reglages>
     */
    public function findALLForTwig(ReglageListQuery $query): array;

    public function getValue(string $name): ?Reglages;

    /** @return Query<mixed, mixed> */
    public function getReglageQuery(GetReglagePaginateListQuery $query): Query;

    public function createCommandFromReglage(int $id): UpdateReglageCommand;
}
