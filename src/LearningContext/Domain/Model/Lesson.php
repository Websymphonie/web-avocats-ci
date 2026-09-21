<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\LearningVideoProvider;
use Websymphonie\LearningContext\Domain\Exception\InvalidLessonVideoException;

final class Lesson
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $moduleId,
        public string $title,
        public ?string $summary = null,
        public int $position = 1,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public string $content = '',
        public ?LearningVideoProvider $videoProvider = null,
        public ?string $videoUrl = null,
        public ?string $externalVideoId = null,
    ) {}

    public function update(string $title, ?string $summary, string $content = '', ?string $videoUrl = null): void
    {
        $this->title = trim($title);
        $this->summary = $summary !== null && trim($summary) !== '' ? trim($summary) : null;
        $this->content = $content;
        $this->setVideo($videoUrl);
    }

    public function setVideo(?string $videoUrl): void
    {
        $videoUrl = $videoUrl !== null && trim($videoUrl) !== '' ? trim($videoUrl) : null;
        if ($videoUrl === null) {
            $this->videoProvider = null;
            $this->videoUrl = null;
            $this->externalVideoId = null;
            return;
        }

        $externalVideoId = self::youtubeIdFromUrl($videoUrl);
        if ($externalVideoId === null) {
            throw new InvalidLessonVideoException('Utilisez une URL YouTube de type watch, youtu.be ou embed.');
        }
        $this->videoProvider = LearningVideoProvider::YOUTUBE;
        $this->videoUrl = $videoUrl;
        $this->externalVideoId = $externalVideoId;
    }

    public function isReadyForPublication(int $resourceCount): bool
    {
        return $this->hasContent() || $this->hasVideo() || $resourceCount > 0;
    }

    public function hasContent(): bool
    {
        return self::hasMeaningfulContent($this->content);
    }

    public function hasVideo(): bool
    {
        return $this->externalVideoId !== null;
    }

    public function videoEmbedUrl(): ?string
    {
        return $this->externalVideoId !== null
            ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($this->externalVideoId)
            : null;
    }

    public static function youtubeIdFromUrl(string $url): ?string
    {
        return YouTubeReference::fromUrl($url)?->externalId;
    }

    public static function hasMeaningfulContent(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text)) !== '';
    }
}
