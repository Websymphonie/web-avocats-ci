<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Context;

use DateTimeImmutable;
use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageDetailsQuery;
use Websymphonie\AdminContext\Application\Usecase\Query\Maintenance\GetMaintenanceDetailsQuery;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglageDetailsQuery;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\ReglageListQuery;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\AdminContext\Presenter\ViewModel\Image\ImageDetailViewModel;
use Websymphonie\AdminContext\Presenter\ViewModel\Maintenance\MaintenanceDetailViewModel;
use Websymphonie\AdminContext\Presenter\ViewModel\Reglage\ReglageDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryBus;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\ListViewModel;

readonly class ContextService implements ContextServiceInterface
{
    public function __construct(
        private QueryBus              $queryBus,
        private CacheServiceInterface $cacheService,
    )
    {
    }

    public function findMaintenance(): ?MaintenanceModel
    {
        /** @var MaintenanceDetailViewModel $model */
        $model = $this->queryBus->handle(new GetMaintenanceDetailsQuery(id: 1));
        return $model->object;
    }

    /**
     * @return list<ReglageModel>
     */
    public function findAll(): array
    {
        /** @var ListViewModel $data */
        $data = $this->queryBus->handle(new ReglageListQuery());
        return $data->items;
    }

    public function getPaginatorPageSize(): int
    {
        return intval($this->getValue('app_paginate_limit')->value);
    }

    public function getValue(string $name): ReglageModel
    {
        /** @var ReglageDetailViewModel $model */
        $model = $this->queryBus->handle(new GetReglageDetailsQuery($name));
        return $model->object;
    }

    public function clearCache(): void
    {
        $this->cacheService->clearAllCache();
    }

    public function findCurrentYear(): int
    {
        return (int)date('Y');
    }

    public function findCurrentDay(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }

    public function monthNumberToName(int $month): string
    {
        $months = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];

        return $months[$month] ?? 'Inconnu';
    }

    /** @return list<string> */
    public function getMonths(): array
    {
        return ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    }

    public function getImage(string $name): ImageModel
    {
        /** @var ImageDetailViewModel $model */
        $model = $this->queryBus->handle(new ImageDetailsQuery($name));
        return $model->object;
    }
}
