<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;

final class SensitiveLogDataSanitizerTest extends TestCase
{
    public function testItRecognizesCommonSensitiveKeyFormats(): void
    {
        $sanitizer = new SensitiveLogDataSanitizer();

        self::assertTrue($sanitizer->isSensitiveKey('password_hash'));
        self::assertTrue($sanitizer->isSensitiveKey('resetToken'));
        self::assertTrue($sanitizer->isSensitiveKey('KKIAPAY_SECRET_KEY'));
        self::assertTrue($sanitizer->isSensitiveKey('csrf_token'));
        self::assertFalse($sanitizer->isSensitiveKey('providerReference'));
    }

    public function testItSanitizesNestedContextAndExtraValues(): void
    {
        $sanitizer = new SensitiveLogDataSanitizer();

        $result = $sanitizer->sanitize([
            'paymentUuid' => 'payment-1',
            'credentials' => [
                'password' => 'old-hash',
                'reset_token' => 'reset-secret',
            ],
        ]);

        self::assertSame('payment-1', $result['paymentUuid']);
        self::assertSame(SensitiveLogDataSanitizer::REDACTED, $result['credentials']['password']);
        self::assertSame(SensitiveLogDataSanitizer::REDACTED, $result['credentials']['reset_token']);
    }

    public function testItSanitizesSensitiveMessageValues(): void
    {
        $sanitizer = new SensitiveLogDataSanitizer();

        $message = $sanitizer->sanitizeMessage('password=old-hash token=reset-secret paymentUuid=payment-1');

        self::assertStringNotContainsString('old-hash', $message);
        self::assertStringNotContainsString('reset-secret', $message);
        self::assertStringContainsString('paymentUuid=payment-1', $message);
    }

    public function testAuditMetadataRejectsObjects(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new SensitiveLogDataSanitizer())->sanitizeMetadata(['payload' => new \stdClass()]);
    }
}
