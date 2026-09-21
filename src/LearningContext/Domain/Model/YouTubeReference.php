<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class YouTubeReference
{
    private function __construct(public string $externalId)
    {
    }

    public static function fromUrl(?string $url): ?self
    {
        $url = $url !== null ? trim($url) : null;
        if ($url === null || $url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

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

        return is_string($externalId) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $externalId) === 1
            ? new self($externalId)
            : null;
    }

    public static function fromHttpsUrl(?string $url): ?self
    {
        $parts = $url !== null ? parse_url(trim($url)) : false;
        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            return null;
        }

        return self::fromUrl($url);
    }

    public function embedUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($this->externalId);
    }
}
