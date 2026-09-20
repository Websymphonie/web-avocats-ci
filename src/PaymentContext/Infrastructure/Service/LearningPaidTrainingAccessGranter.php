<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\LearningContext\Application\Usecase\Command\GrantPaidTrainingAccessCommand;
use Websymphonie\LearningContext\Application\Usecase\CommandHandler\GrantPaidTrainingAccessHandler;
use Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface;

final readonly class LearningPaidTrainingAccessGranter implements PaidTrainingAccessGranterInterface
{
    public function __construct(private GrantPaidTrainingAccessHandler $handler) {}

    public function grant(int $userId, int $trainingId): void
    {
        ($this->handler)(new GrantPaidTrainingAccessCommand($trainingId, $userId));
    }
}
