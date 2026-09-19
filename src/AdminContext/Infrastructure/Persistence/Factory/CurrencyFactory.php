<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Factory;

use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;

final class CurrencyFactory
{
    /**
     * @param list<Currencies> $entities
     * @return list<CurrencyModel>
     */
    public static function fromEntityList(array $entities): array
    {
        return array_map(fn(Currencies $entity) => self::fromEntity($entity), $entities);
    }

    /**
     * @param Currencies|null $entity
     * @return CurrencyModel|null
     */
    public static function fromEntity(?Currencies $entity): ?CurrencyModel
    {
        if ($entity === null) {
            return null;
        }

        return new CurrencyModel(
            id: $entity->getId(),
            currencyCode: $entity->getCurrencyCode(),
            currencyName: $entity->getCurrencyName(),
            leftSymbol: $entity->getLeftSymbol(),
            rightSymbol: $entity->getRightSymbol(),
            decimalSymbol: $entity->getDecimalSymbol(),
            decimalPlace: $entity->getDecimalPlace(),
            thousandsSeparator: $entity->getThousandsSeparator(),
            exchangedValue: $entity->getExchangedValue(),
            codeiso: $entity->getCodeiso(),
            lang: $entity->getLang(),
            langCode: $entity->getLangCode(),
            isActive: $entity->getIsActive(),
        );
    }
}
