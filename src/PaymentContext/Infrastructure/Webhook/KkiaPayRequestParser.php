<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Webhook;

use SensitiveParameter;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

final class KkiaPayRequestParser extends AbstractRequestParser
{
    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new IsJsonRequestMatcher(),
            new MethodRequestMatcher('POST'),
        ]);
    }

    protected function doParse(Request $request, #[SensitiveParameter] string $secret): RemoteEvent
    {
        if ($request->getContentTypeFormat() !== 'json') {
            throw new RejectWebhookException(406, 'KkiaPay webhook must use JSON.');
        }

        $receivedSecret = trim((string)$request->headers->get('x-kkiapay-secret'));
        if ($secret === '' || $receivedSecret === '' || !hash_equals($secret, $receivedSecret)) {
            throw new RejectWebhookException(401, 'Invalid KkiaPay webhook secret.');
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RejectWebhookException(406, 'Malformed KkiaPay webhook payload.', $exception);
        }
        if (!is_array($payload)) {
            throw new RejectWebhookException(406, 'KkiaPay webhook payload must be a JSON object.');
        }

        $event = $payload['event'] ?? null;
        $transactionId = $payload['transactionId'] ?? null;
        $partnerId = $payload['partnerId'] ?? null;
        $successful = $payload['isPaymentSucces'] ?? null;
        $amount = $this->normalizeAmount($payload['amount'] ?? null);
        if (!is_string($event) || !in_array($event, ['transaction.success', 'transaction.failed'], true)) {
            throw new RejectWebhookException(406, 'Unsupported KkiaPay webhook event.');
        }
        if (!is_string($transactionId) || trim($transactionId) === '' || !is_string($partnerId) || trim($partnerId) === '' || !is_bool($successful) || $amount === null) {
            throw new RejectWebhookException(406, 'Incomplete KkiaPay webhook payload.');
        }

        $eventIsSuccess = $event === 'transaction.success';
        if ($successful !== $eventIsSuccess) {
            throw new RejectWebhookException(406, 'Incoherent KkiaPay webhook payload.');
        }

        $payload['transactionId'] = trim($transactionId);
        $payload['partnerId'] = trim($partnerId);
        $payload['amount'] = $amount;
        $payload['event'] = $event;

        return new RemoteEvent($event, $event . ':' . trim($transactionId), $payload);
    }

    private function normalizeAmount(mixed $amount): ?int
    {
        if (!is_int($amount) && !is_float($amount) && !is_string($amount)) {
            return null;
        }
        if (!is_numeric($amount)) {
            return null;
        }

        $normalized = (int)$amount;
        return $normalized > 0 && (float)$amount === (float)$normalized ? $normalized : null;
    }
}
