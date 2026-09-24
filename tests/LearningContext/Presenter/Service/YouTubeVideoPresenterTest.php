<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Presenter\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;
use Websymphonie\LearningContext\Presenter\Service\YouTubeVideoPresenter;

final class YouTubeVideoPresenterTest extends TestCase
{
    public function testCreatesPrivacyEnhancedEmbedAndCanonicalReference(): void
    {
        $presenter = new YouTubeVideoPresenter();
        $source = new ExternalVideoSource(VideoProvider::YOUTUBE, 'M7lc1UVf-VE');

        self::assertSame('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', $presenter->embedUrl($source));
        self::assertSame('https://www.youtube.com/watch?v=M7lc1UVf-VE', $presenter->referenceUrl($source));
    }

    public function testDoesNotPretendUnsupportedProvidersArePlayable(): void
    {
        $presenter = new YouTubeVideoPresenter();
        $source = new ExternalVideoSource(VideoProvider::MUX, 'asset-id-123');

        self::assertNull($presenter->embedUrl($source));
        self::assertNull($presenter->referenceUrl($source));
    }

    public function testMuxPlaybackIdIsRestoredAsTheBackofficeFormReference(): void
    {
        $presenter = new YouTubeVideoPresenter();
        $source = new ExternalVideoSource(VideoProvider::MUX, 'AbCdEf0123456789_-');

        self::assertSame('AbCdEf0123456789_-', $presenter->referenceForForm($source));
        self::assertNull($presenter->embedUrl($source));
    }
}
