<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
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
        public ?ExternalVideoSource $videoSource = null,
    ) {}

    public function update(string $title, ?string $summary, string $content = '', ?ExternalVideoSource $videoSource = null): void
    {
        $this->title = trim($title);
        $this->summary = $summary !== null && trim($summary) !== '' ? trim($summary) : null;
        $this->content = $content;
        $this->videoSource = $videoSource;
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
        return $this->videoSource !== null;
    }

    public static function hasMeaningfulContent(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text)) !== '';
    }
}
