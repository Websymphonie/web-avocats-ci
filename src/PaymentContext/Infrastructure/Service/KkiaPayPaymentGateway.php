<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Websymphonie\PaymentContext\Application\Model\PaymentInitialization;
use Websymphonie\PaymentContext\Application\Service\PaymentGatewayInterface;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Model\Payment;

final readonly class KkiaPayPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        #[Autowire('%env(KKIAPAY_PUBLIC_KEY)%')]
        private string $publicKey,
        #[Autowire('%env(bool:KKIAPAY_SANDBOX)%')]
        private bool $sandbox,
    ) {
    }

    public function provider(): PaymentProvider
    {
        return PaymentProvider::KKIAPAY;
    }

    public function initializePayment(Payment $payment): PaymentInitialization
    {
        return new PaymentInitialization(
            provider: $this->provider(),
            providerReference: null,
            publicData: [
                'publicKey' => $this->publicKey,
                'sandbox' => $this->sandbox,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'partnerId' => $payment->uuid,
            ],
        );
    }
}
