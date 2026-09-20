<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

use Websymphonie\PaymentContext\Application\Model\TrainingReference;

interface TrainingCatalogInterface
{
    public function getById(int $trainingId): TrainingReference;
    public function getByUuid(string $uuid): TrainingReference;

    /**
     * @param list<int> $trainingIds
     * @return list<TrainingReference>
     */
    public function getByIds(array $trainingIds): array;

    /** @return list<TrainingReference> */
    public function list(int $page, int $limit): array;
}
