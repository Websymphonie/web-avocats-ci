<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Infrastructure\Service;

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Exception\VideoPlaybackUnavailableException;
use Websymphonie\LearningContext\Infrastructure\Service\MuxPlaybackTokenSigner;

final class MuxPlaybackTokenSignerTest extends TestCase
{
    public function testSignsPlaybackIdWithExpectedClaimsAndTtl(): void
    {
        [$privateKey, $publicKey] = $this->createKeyPair();
        $signer = new MuxPlaybackTokenSigner('test-key-id', base64_encode($privateKey), 'test-restriction', 1800);
        $before = time();

        $token = $signer->signPlayback('AbCdEf0123456789_-');
        $claims = JWT::decode($token, new Key($publicKey, 'RS256'));
        $header = json_decode(base64_decode(explode('.', $token)[0]), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('AbCdEf0123456789_-', $claims->sub);
        self::assertSame('v', $claims->aud);
        self::assertSame('test-restriction', $claims->playback_restriction_id);
        self::assertGreaterThanOrEqual($before + 1800, $claims->exp);
        self::assertSame('test-key-id', $header['kid']);
        self::assertSame('RS256', $header['alg']);
    }

    public function testAcceptsEscapedNewlinesAndNoOptionalRestriction(): void
    {
        [$privateKey, $publicKey] = $this->createKeyPair();
        $signer = new MuxPlaybackTokenSigner('test-key-id', str_replace("\n", '\\n', $privateKey), '', 3600);

        $claims = JWT::decode($signer->signPlayback('AbCdEf0123456789_-'), new Key($publicKey, 'RS256'));

        self::assertObjectNotHasProperty('playback_restriction_id', $claims);
    }

    public function testMissingConfigurationFailsClosed(): void
    {
        $signer = new MuxPlaybackTokenSigner('', '', '', 14400);

        $this->expectException(VideoPlaybackUnavailableException::class);
        $signer->signPlayback('AbCdEf0123456789_-');
    }

    /** @return array{string, string} */
    private function createKeyPair(): array
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        self::assertInstanceOf(OpenSSLAsymmetricKey::class, $key);
        self::assertTrue(openssl_pkey_export($key, $privateKey));
        $details = openssl_pkey_get_details($key);
        self::assertIsArray($details);
        self::assertIsString($details['key'] ?? null);

        return [$privateKey, $details['key']];
    }
}
