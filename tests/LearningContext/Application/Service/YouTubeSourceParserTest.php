<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\YouTubeSourceParser;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Exception\InvalidLessonVideoException;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;

final class YouTubeSourceParserTest extends TestCase
{
    private YouTubeSourceParser $parser;

    protected function setUp(): void
    {
        $this->parser = new YouTubeSourceParser();
    }

    public function testParsesWatchShortAndEmbedReferences(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=M7lc1UVf-VE',
            'https://youtu.be/M7lc1UVf-VE',
            'https://www.youtube.com/embed/M7lc1UVf-VE',
        ] as $reference) {
            $source = $this->parser->parseLessonReference($reference);

            self::assertNotNull($source);
            self::assertSame(VideoProvider::YOUTUBE, $source->provider);
            self::assertSame('M7lc1UVf-VE', $source->externalId);
        }
    }

    public function testBlankReferenceMeansNoVideo(): void
    {
        self::assertNull($this->parser->parseLessonReference(' '));
    }

    public function testRejectsHttpForLesson(): void
    {
        $this->expectException(InvalidLessonVideoException::class);

        $this->parser->parseLessonReference('http://youtube.com/watch?v=M7lc1UVf-VE');
    }

    public function testRejectsUnsupportedProviderForLesson(): void
    {
        $this->expectException(InvalidLessonVideoException::class);

        $this->parser->parseLessonReference('https://vimeo.com/123456');
    }

    public function testParsesMuxPlaybackIdForLessons(): void
    {
        $source = $this->parser->parseLessonReference('AbCdEf0123456789_-', VideoProvider::MUX);

        self::assertNotNull($source);
        self::assertSame(VideoProvider::MUX, $source->provider);
        self::assertSame('AbCdEf0123456789_-', $source->externalId);
    }

    public function testRejectsMuxUrlOrInvalidPlaybackId(): void
    {
        $this->expectException(InvalidLessonVideoException::class);

        $this->parser->parseLessonReference('https://stream.mux.com/AbCdEf0123456789_.m3u8', VideoProvider::MUX);
    }

    public function testRejectsHttpForLive(): void
    {
        $this->expectException(InvalidLiveTrainingDetailsException::class);

        $this->parser->parseLiveReference('http://youtu.be/M7lc1UVf-VE');
    }
}
