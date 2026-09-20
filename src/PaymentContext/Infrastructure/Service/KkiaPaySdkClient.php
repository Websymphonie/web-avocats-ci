<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Kkiapay\Kkiapay;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class KkiaPaySdkClient implements KkiaPaySdkClientInterface
{
    private Kkiapay $client;

    public function __construct(
        #[Autowire('%env(KKIAPAY_PUBLIC_KEY)%')]
        string $publicKey,
        #[Autowire('%env(KKIAPAY_PRIVATE_KEY)%')]
        string $privateKey,
        #[Autowire('%env(KKIAPAY_SECRET_KEY)%')]
        string $secretKey,
        #[Autowire('%env(bool:KKIAPAY_SANDBOX)%')]
        bool $sandbox,
    ) {
        $this->client = new Kkiapay($publicKey, $privateKey, $secretKey, $sandbox);
    }

    public function verifyTransaction(string $transactionId): mixed
    {
        return $this->client->verifyTransaction($transactionId);
    }
}
