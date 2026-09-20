<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryUser;
use Websymphonie\PaymentContext\Domain\Model\Payment;

final readonly class PaymentListItem
{
    public function __construct(public Payment $payment, public ?TrainingReference $training, public ?UserDirectoryUser $user) {}
}
