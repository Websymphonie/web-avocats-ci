<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Exception\InvalidEditorialVideoDetailsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEditorialVideoTransitionException;

final class EditorialVideo
{
    /** @param list<Tag> $tags */
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $description,
        public VideoProvider $provider,
        public string $videoUrl,
        public ?string $externalVideoId = null,
        public EditorialVideoStatus $status = EditorialVideoStatus::DRAFT,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public array $tags = [],
        public ?EditorialVideoCategory $category = null,
    ) {
        self::assertUrl($provider, $videoUrl, allowEmpty: true);
        $this->externalVideoId = self::youtubeIdFromUrl($provider, $videoUrl);
    }

    /** @param list<Tag> $tags */
    public function update(string $title, string $slug, ?string $excerpt, string $description, VideoProvider $provider, string $videoUrl, array $tags = [], ?EditorialVideoCategory $category = null): void
    {
        self::assertUrl($provider, $videoUrl, allowEmpty: true);
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->description = $description;
        $this->provider = $provider;
        $this->videoUrl = $videoUrl;
        $this->externalVideoId = self::youtubeIdFromUrl($provider, $videoUrl);
        if ($this->status === EditorialVideoStatus::DRAFT) {
            $this->slug = $slug;
        }
        $this->replaceTags($tags);
        $this->category = $category;
    }

    /** @param list<Tag> $tags */
    public function replaceTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function publish(): void
    {
        if ($this->status !== EditorialVideoStatus::DRAFT) {
            throw new InvalidEditorialVideoTransitionException('Seul un brouillon peut être publié.');
        }
        if (trim($this->title) === '') {
            throw new InvalidEditorialVideoDetailsException('Une vidéo doit avoir un titre pour être publiée.');
        }
        self::assertUrl($this->provider, $this->videoUrl);
        $this->externalVideoId = self::youtubeIdFromUrl($this->provider, $this->videoUrl);
        $this->status = EditorialVideoStatus::PUBLISHED;
        $this->publishedAt ??= new DateTimeImmutable();
    }

    public function archive(): void
    {
        if ($this->status !== EditorialVideoStatus::PUBLISHED) {
            throw new InvalidEditorialVideoTransitionException('Seule une vidéo publiée peut être archivée.');
        }
        $this->status = EditorialVideoStatus::ARCHIVED;
    }

    public static function youtubeIdFromUrl(VideoProvider $provider, string $url): ?string
    {
        if ($provider !== VideoProvider::YOUTUBE || trim($url) === '') {
            return null;
        }
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) && str_starts_with($path, 'watch')) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            return isset($query['v']) && is_string($query['v']) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $query['v']) === 1 ? $query['v'] : null;
        }
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true) && preg_match('/^([A-Za-z0-9_-]{6,})/', $path, $match) === 1) {
            return $match[1];
        }
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true) && preg_match('#^(?:embed|shorts)/([A-Za-z0-9_-]{6,})#', $path, $match) === 1) {
            return $match[1];
        }
        return null;
    }

    public function youtubeEmbedUrl(): ?string
    {
        return $this->provider === VideoProvider::YOUTUBE && $this->externalVideoId !== null
            ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($this->externalVideoId)
            : null;
    }

    private static function assertUrl(VideoProvider $provider, string $url, bool $allowEmpty = false): void
    {
        $url = trim($url);
        if ($allowEmpty && $url === '') {
            return;
        }
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            throw new InvalidEditorialVideoDetailsException('L’URL de la vidéo doit être une URL http ou https valide.');
        }
        if ($provider === VideoProvider::YOUTUBE && self::youtubeIdFromUrl($provider, $url) === null) {
            throw new InvalidEditorialVideoDetailsException('Utilisez une URL YouTube de type watch, youtu.be ou embed.');
        }
    }
}
