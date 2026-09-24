<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;

final class ExternalVideoSourceTest extends TestCase
{
    public function testSourceAcceptsSupportedProviderWithoutProviderSpecificBehavior(): void
    {
        $source = new ExternalVideoSource(VideoProvider::CLOUDFLARE_STREAM, 'cloudflare-asset-01');

        self::assertSame(VideoProvider::CLOUDFLARE_STREAM, $source->provider);
        self::assertSame('cloudflare-asset-01', $source->externalId);
    }

    public function testExternalIdCannotBeBlank(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExternalVideoSource(VideoProvider::YOUTUBE, '   ');
    }

    public function testExternalIdCannotExceedMaximumLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExternalVideoSource(VideoProvider::MUX, str_repeat('x', 129));
    }
}
