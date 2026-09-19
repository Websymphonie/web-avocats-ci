<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Factory;

use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;

final class ReglageFactory
{
    /**
     * @param list<Reglages> $entities
     * @return list<ReglageModel>
     */
    public static function fromEntityList(array $entities): array
    {
        return array_map(fn(Reglages $reglage) => self::fromEntity($reglage), $entities);
    }

    /**
     * @param Reglages|null $reglage
     * @return ReglageModel|null
     */
    public static function fromEntity(?Reglages $reglage): ?ReglageModel
    {
        if ($reglage === null) {
            return null;
        }
        return new ReglageModel(
            id: $reglage->getId(),
            name: $reglage->getName(),
            label: $reglage->getLabel(),
            value: $reglage->getValue(),
            type: $reglage->getType(),
            displayValue: $reglage->displayValue(),
            updatedAt: $reglage->getUpdatedAt(),
        );
    }
}
