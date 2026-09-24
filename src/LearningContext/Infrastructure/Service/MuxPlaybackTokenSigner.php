<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Service;

use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;
use Websymphonie\LearningContext\Application\Exception\VideoPlaybackUnavailableException;
use Websymphonie\LearningContext\Application\Service\MuxPlaybackTokenSignerInterface;

final readonly class MuxPlaybackTokenSigner implements MuxPlaybackTokenSignerInterface
{
    public function __construct(
        private string $signingKeyId,
        private string $privateKey,
        private string $playbackRestrictionId,
        private int $tokenTtlSeconds,
    ) {
    }

    public function signPlayback(string $playbackId): string
    {
        if (preg_match('/^[A-Za-z0-9_-]{16,64}$/', $playbackId) !== 1
            || trim($this->signingKeyId) === ''
            || trim($this->privateKey) === ''
            || $this->tokenTtlSeconds < 300
            || $this->tokenTtlSeconds > 86400
        ) {
            throw new VideoPlaybackUnavailableException('Mux playback signing is not configured correctly.');
        }

        $key = $this->loadPrivateKey();
        $claims = [
            'sub' => $playbackId,
            'aud' => 'v',
            'exp' => time() + $this->tokenTtlSeconds,
        ];

        if (trim($this->playbackRestrictionId) !== '') {
            $claims['playback_restriction_id'] = trim($this->playbackRestrictionId);
        }

        try {
            return JWT::encode($claims, $key, 'RS256', trim($this->signingKeyId));
        } catch (\Throwable) {
            throw new VideoPlaybackUnavailableException('Mux playback signing is not available.');
        }
    }

    private function loadPrivateKey(): OpenSSLAsymmetricKey
    {
        $configuredKey = trim($this->privateKey);
        $decoded = base64_decode($configuredKey, true);
        $pem = $decoded !== false && str_contains($decoded, '-----BEGIN')
            ? $decoded
            : str_replace(['\\r\\n', '\\n', '\\r'], ["\n", "\n", "\n"], $configuredKey);

        $key = openssl_pkey_get_private($pem);
        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw new VideoPlaybackUnavailableException('Mux playback signing key is invalid.');
        }

        $details = openssl_pkey_get_details($key);
        if (!is_array($details) || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_RSA || ($details['bits'] ?? 0) < 2048) {
            throw new VideoPlaybackUnavailableException('Mux playback signing key is invalid.');
        }

        return $key;
    }
}
