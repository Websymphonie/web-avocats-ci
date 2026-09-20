<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\PaymentContext\Application\Model\TrainingReference;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;

final readonly class LearningTrainingCatalog implements TrainingCatalogInterface
{
    public function __construct(private TrainingRepositoryInterface $trainings) {}

    public function getById(int $trainingId): TrainingReference
    {
        return self::map($this->trainings->getById($trainingId));
    }

    public function getByUuid(string $uuid): TrainingReference
    {
        return self::map($this->trainings->getByUuid($uuid));
    }

    public function list(int $page, int $limit): array
    {
        $result = $this->trainings->list(null, null, null, TrainingAccessType::PAID, null, null, null, $page, $limit);
        return array_map(static fn ($training): TrainingReference => self::map($training), $result->items);
    }

    private static function map(object $training): TrainingReference
    {
        return new TrainingReference($training->id, $training->uuid, $training->title, $training->status->value, $training->accessType->value);
    }
}
