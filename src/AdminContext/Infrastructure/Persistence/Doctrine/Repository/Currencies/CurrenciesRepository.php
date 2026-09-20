<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Currencies;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\AdminContext\Domain\Exception\NoDataFoundException;
use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;
use Websymphonie\AdminContext\Domain\Repository\Currencies\CurrencyModelRepositoryInterface;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\CurrencyFactory;

/** @extends ServiceEntityRepository<Currencies> */
class CurrenciesRepository extends ServiceEntityRepository implements CurrencyModelRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currencies::class);
    }

    /**
     * @param int $id
     * @return CurrencyModel
     */
    public function getById(int $id): CurrencyModel
    {
        $currency = $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if ($currency === null) {
            throw new NoDataFoundException(message: 'Currency not found');
        }

        return CurrencyFactory::fromEntity($currency);
    }

    /**
     * @param int $id
     * @return Currencies
     */
    public function getByEntityId(int $id): Currencies
    {
        $currency = $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if ($currency === null) {
            throw new NoDataFoundException(message: 'Currency not found');
        }

        return $currency;
    }

    /**
     * @return CurrencyModel
     */
    public function isActive(): CurrencyModel
    {
        $currency = $this->createQueryBuilder('c')
            ->where('c.isActive = TRUE')
            ->getQuery()->getOneOrNullResult();
        if ($currency === null) {
            throw new NoDataFoundException(message: 'Currency not found');
        }

        return CurrencyFactory::fromEntity($currency);
    }

    /** @return list<CurrencyModel> */
    public function listActive(): array
    {
        $currencies = $this->createQueryBuilder('c')
            ->where('c.isActive = TRUE')
            ->andWhere('c.currencyCode IS NOT NULL')
            ->andWhere('c.currencyCode <> :empty')
            ->setParameter('empty', '')
            ->orderBy('c.currencyName', 'ASC')
            ->addOrderBy('c.currencyCode', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var list<Currencies> $currencies */
        return CurrencyFactory::fromEntityList($currencies);
    }

    public function findActiveByCode(string $code): ?CurrencyModel
    {
        $normalizedCode = strtoupper(trim($code));
        if ($normalizedCode === '') {
            return null;
        }

        $currency = $this->createQueryBuilder('c')
            ->where('c.isActive = TRUE')
            ->andWhere('UPPER(c.currencyCode) = :code')
            ->setParameter('code', $normalizedCode)
            ->getQuery()
            ->getOneOrNullResult();

        /** @var Currencies|null $currency */
        return CurrencyFactory::fromEntity($currency);
    }
}
