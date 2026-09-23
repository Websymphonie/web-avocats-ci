<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LawyerContext\Infrastructure\Import;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyPortraitDownloadException;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyPortraitDownloader;

final class LegacyPortraitDownloaderTest extends TestCase
{
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC';
    private const JPEG = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/AV//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/AV//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Al//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IT//2Q==';
    private const URL = 'https://app.ordredesavocats-ci.net/storage/Profile/sample.png';

    public function testAcceptsDecodablePngAndJpegOnlyWhenMimeMatchesTheBytes(): void
    {
        foreach ([[self::PNG, 'image/png', 'png'], [self::JPEG, 'image/jpeg', 'jpg']] as [$encoded, $mimeType, $extension]) {
            $bytes = base64_decode($encoded, true);
            self::assertIsString($bytes);
            $client = new MockHttpClient(static fn (): MockResponse => self::response($bytes, $mimeType));

            $portrait = (new LegacyPortraitDownloader($client, 5 * 1024 * 1024))->download(self::URL);

            self::assertSame($mimeType, $portrait->mimeType);
            self::assertSame($extension, $portrait->extension);
            self::assertSame($bytes, $portrait->contents);
        }
    }

    public function testRetriesTransientServerErrorThenSucceeds(): void
    {
        $attempts = 0;
        $bytes = base64_decode(self::PNG, true);
        self::assertIsString($bytes);
        $client = new MockHttpClient(static function () use (&$attempts, $bytes): MockResponse {
            $attempts++;

            return $attempts === 1
                ? new MockResponse('', ['http_code' => 500])
                : self::response($bytes, 'image/png');
        });

        $portrait = (new LegacyPortraitDownloader($client, 5 * 1024 * 1024))->download(self::URL);

        self::assertSame(2, $attempts);
        self::assertSame('image/png', $portrait->mimeType);
    }

    public function testDoesNotRetryNotFoundOrPermanentServerErrorBeyondThreeAttempts(): void
    {
        $notFoundClient = new MockHttpClient(static fn (): MockResponse => new MockResponse('not found', ['http_code' => 404]));
        try {
            (new LegacyPortraitDownloader($notFoundClient, 5 * 1024 * 1024))->download(self::URL);
            self::fail('404 doit être signalé.');
        } catch (LegacyPortraitDownloadException $exception) {
            self::assertSame('download_failed', $exception->stage);
        }

        $attempts = 0;
        $serverErrorClient = new MockHttpClient(static function () use (&$attempts): MockResponse {
            $attempts++;

            return new MockResponse('', ['http_code' => 500]);
        });
        try {
            (new LegacyPortraitDownloader($serverErrorClient, 5 * 1024 * 1024))->download(self::URL);
            self::fail('500 persistant doit être signalé.');
        } catch (LegacyPortraitDownloadException $exception) {
            self::assertSame('download_failed', $exception->stage);
        }
        self::assertSame(3, $attempts);
    }

    public function testRejectsHtml200OversizeAndForeignHost(): void
    {
        $htmlClient = new MockHttpClient(static fn (): MockResponse => new MockResponse('<html>404</html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'image/png']]));
        try {
            (new LegacyPortraitDownloader($htmlClient, 5 * 1024 * 1024))->download(self::URL);
            self::fail('Un HTML HTTP 200 ne doit pas être importé.');
        } catch (LegacyPortraitDownloadException $exception) {
            self::assertSame('invalid_image', $exception->stage);
        }

        $bytes = base64_decode(self::PNG, true);
        self::assertIsString($bytes);
        $largeClient = new MockHttpClient(static fn (): MockResponse => new MockResponse($bytes . $bytes, ['http_code' => 200, 'response_headers' => ['content-type' => 'image/png', 'content-length' => (string) (strlen($bytes) * 2)]]));
        try {
            (new LegacyPortraitDownloader($largeClient, 32))->download(self::URL);
            self::fail('Un fichier dépassant la limite doit être refusé.');
        } catch (LegacyPortraitDownloadException $exception) {
            self::assertSame('invalid_image', $exception->stage);
        }

        $called = false;
        $foreignClient = new MockHttpClient(static function () use (&$called): MockResponse {
            $called = true;

            return new MockResponse('');
        });
        try {
            (new LegacyPortraitDownloader($foreignClient, 5 * 1024 * 1024))->download('https://example.test/storage/Profile/a.png');
            self::fail('Un domaine tiers doit être refusé.');
        } catch (LegacyPortraitDownloadException $exception) {
            self::assertSame('download_failed', $exception->stage);
        }
        self::assertFalse($called);
    }

    private static function response(string $body, string $mimeType): MockResponse
    {
        return new MockResponse($body, [
            'http_code' => 200,
            'response_headers' => ['content-type' => $mimeType, 'content-length' => (string) strlen($body)],
        ]);
    }
}
