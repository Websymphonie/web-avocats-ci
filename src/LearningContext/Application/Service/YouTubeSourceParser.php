<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Exception\InvalidLessonVideoException;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;

final class YouTubeSourceParser
{
    public function parseLessonReference(?string $reference, VideoProvider $provider = VideoProvider::YOUTUBE): ?ExternalVideoSource
    {
        $reference = $reference !== null ? trim($reference) : null;
        if ($reference === null || $reference === '') {
            return null;
        }

        if ($provider === VideoProvider::MUX) {
            if (preg_match('/^[A-Za-z0-9_-]{16,64}$/', $reference) !== 1) {
                throw new InvalidLessonVideoException('Saisissez un Playback ID Mux valide, pas une URL ni un Asset ID.');
            }

            return new ExternalVideoSource(VideoProvider::MUX, $reference);
        }

        if ($provider !== VideoProvider::YOUTUBE) {
            throw new InvalidLessonVideoException('Ce fournisseur vidéo n’est pas disponible pour les leçons.');
        }

        return $this->parse($reference, false);
    }

    public function parseLiveReference(?string $url): ?ExternalVideoSource
    {
        return $this->parse($url, true);
    }

    private function parse(?string $url, bool $live): ?ExternalVideoSource
    {
        $url = $url !== null ? trim($url) : null;
        if ($url === null || $url === '') {
            return null;
        }

        $parts = parse_url($url);
        $externalId = is_array($parts) ? $this->extractId($parts) : null;
        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || $externalId === null) {
            if ($live) {
                throw new InvalidLiveTrainingDetailsException('Utilisez une URL YouTube HTTPS valide de type watch, youtu.be ou embed.');
            }

            throw new InvalidLessonVideoException('Utilisez une URL YouTube HTTPS de type watch, youtu.be ou embed.');
        }

        return new ExternalVideoSource(VideoProvider::YOUTUBE, $externalId);
    }

    /** @param array<string, mixed> $parts */
    private function extractId(array $parts): ?string
    {
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $externalId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) && $path === 'watch') {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $externalId = isset($query['v']) && is_string($query['v']) ? $query['v'] : null;
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $externalId = preg_match('/^([A-Za-z0-9_-]{6,})/', $path, $match) === 1 ? $match[1] : null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $externalId = preg_match('#^(?:embed|shorts)/([A-Za-z0-9_-]{6,})#', $path, $match) === 1 ? $match[1] : null;
        }

        return is_string($externalId) && preg_match('/^[A-Za-z0-9_-]{6,128}$/', $externalId) === 1
            ? $externalId
            : null;
    }
}
