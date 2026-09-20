<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Repository\Currencies;

use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;

interface CurrencyModelRepositoryInterface
{
    public function getById(int $id): ?CurrencyModel;

    public function getByEntityId(int $id): ?Currencies;

    public function isActive(): ?CurrencyModel;

    /** @return list<CurrencyModel> */
    public function listActive(): array;

    public function findActiveByCode(string $code): ?CurrencyModel;
}
