<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Exception\InvalidEditorialVideoDetailsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEditorialVideoTransitionException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;

final class EditorialVideoTest extends TestCase
{
    public function testDraftAcceptsAValidYoutubeReference(): void
    {
        $video = $this->video('https://www.youtube.com/watch?v=abcDEF_123');
        self::assertSame(EditorialVideoStatus::DRAFT, $video->status);
        self::assertSame('abcDEF_123', $video->externalVideoId);
    }

    public function testYoutubeUrlsAreParsedAndUnknownHostsAreRejected(): void
    {
        self::assertSame('abcDEF_123', EditorialVideo::youtubeIdFromUrl(VideoProvider::YOUTUBE, 'https://youtu.be/abcDEF_123'));
        self::assertSame('abcDEF_123', EditorialVideo::youtubeIdFromUrl(VideoProvider::YOUTUBE, 'https://www.youtube-nocookie.com/embed/abcDEF_123'));
        $this->expectException(InvalidEditorialVideoDetailsException::class);
        $this->video('https://example.com/video', VideoProvider::YOUTUBE);
    }

    public function testPublishSetsTimestampAndArchiveOnlyWorksAfterPublication(): void
    {
        $video = $this->video('https://youtu.be/abcDEF_123');
        $video->publish();
        self::assertSame(EditorialVideoStatus::PUBLISHED, $video->status);
        self::assertNotNull($video->publishedAt);
        $video->archive();
        self::assertSame(EditorialVideoStatus::ARCHIVED, $video->status);
    }

    public function testPublishedVideoCannotBePublishedOrChangeItsSlug(): void
    {
        $video = $this->video('https://youtu.be/abcDEF_123');
        $video->publish();
        $video->update('Nouveau titre', 'nouveau-titre', null, '', VideoProvider::YOUTUBE, 'https://youtu.be/abcDEF_123');
        self::assertSame('video-de-test', $video->slug);
        $this->expectException(InvalidEditorialVideoTransitionException::class);
        $video->publish();
    }

    public function testExternalUrlIsAcceptedWithoutAnIframeId(): void
    {
        $video = $this->video('https://example.com/video', VideoProvider::EXTERNAL_URL);
        self::assertNull($video->externalVideoId);
        self::assertNull($video->youtubeEmbedUrl());
    }

    private function video(string $url, VideoProvider $provider = VideoProvider::YOUTUBE): EditorialVideo
    {
        return new EditorialVideo(1, 'uuid', 'Vidéo de test', 'video-de-test', null, '<p>Description</p>', $provider, $url);
    }
}
