<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Import;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class LegacyPortraitDownloader
{
    private const SOURCE_HOST = 'app.ordredesavocats-ci.net';
    private const FORMATS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(private HttpClientInterface $httpClient, private int $galleryMediaMaxSize)
    {
    }

    public function download(string $url): DownloadedLegacyPortrait
    {
        $this->assertAllowedUrl($url);
        $lastException = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = $this->httpClient->request('GET', $url, [
                    'headers' => ['Accept' => 'image/jpeg,image/png,image/webp'],
                    'max_redirects' => 0,
                    'timeout' => 15,
                    'max_duration' => 30,
                ]);
                $status = $response->getStatusCode();
                if ($status >= 500 || $status === 429) {
                    $response->cancel();
                    if ($attempt < 3) {
                        usleep(250_000);
                        continue;
                    }

                    throw new LegacyPortraitDownloadException('download_failed', sprintf('Réponse HTTP transitoire persistante (%d).', $status));
                }
                if ($status !== 200) {
                    $response->cancel();

                    throw new LegacyPortraitDownloadException('download_failed', sprintf('Réponse HTTP inattendue (%d).', $status));
                }

                return $this->readAndValidate($response);
            } catch (TransportExceptionInterface $exception) {
                $lastException = $exception;
                if ($attempt < 3) {
                    usleep(250_000);
                    continue;
                }
            }
        }

        throw new LegacyPortraitDownloadException('download_failed', 'Échec réseau après trois tentatives.', $lastException);
    }

    private function assertAllowedUrl(string $url): void
    {
        $parts = parse_url($url);
        if (!is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || strtolower((string) ($parts['host'] ?? '')) !== self::SOURCE_HOST
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
            || !str_starts_with((string) ($parts['path'] ?? ''), '/storage/Profile/')) {
            throw new LegacyPortraitDownloadException('download_failed', 'URL source refusée : seuls les portraits HTTPS du domaine historique sont autorisés.');
        }
    }

    private function readAndValidate(ResponseInterface $response): DownloadedLegacyPortrait
    {
        $headers = $response->getHeaders(false);
        $contentType = strtolower(trim(explode(';', $headers['content-type'][0] ?? '')[0]));
        $contentLength = isset($headers['content-length'][0]) ? (int) $headers['content-length'][0] : null;
        if ($contentLength !== null && $contentLength > $this->galleryMediaMaxSize) {
            $response->cancel();

            throw new LegacyPortraitDownloadException('invalid_image', 'Image supérieure à la limite de taille Media.');
        }

        $contents = '';
        try {
            foreach ($this->httpClient->stream($response, 15) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }
                $contents .= $chunk->getContent();
                if (strlen($contents) > $this->galleryMediaMaxSize) {
                    throw new LegacyPortraitDownloadException('invalid_image', 'Fichier supérieur à la limite de taille Media.');
                }
            }
        } finally {
            $response->cancel();
        }
        if ($contents === '') {
            throw new LegacyPortraitDownloadException('invalid_image', 'Fichier vide ou supérieur à la limite de taille Media.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);
        $imageInfo = @getimagesizefromstring($contents);
        if (!is_string($mimeType)
            || !isset(self::FORMATS[$mimeType])
            || $contentType !== $mimeType
            || !is_array($imageInfo)
            || $imageInfo[0] < 1
            || $imageInfo[1] < 1
            || image_type_to_mime_type($imageInfo[2]) !== $mimeType) {
            throw new LegacyPortraitDownloadException('invalid_image', 'La réponse ne contient pas une image décodable du type annoncé.');
        }

        return new DownloadedLegacyPortrait($contents, $mimeType, self::FORMATS[$mimeType]);
    }
}
