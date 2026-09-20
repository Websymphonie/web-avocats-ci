<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;

final readonly class PaymentInitialization
{
    /** @param array<string, scalar> $publicData */
    public function __construct(public PaymentProvider $provider, public ?string $providerReference, public array $publicData = []) {}
}
