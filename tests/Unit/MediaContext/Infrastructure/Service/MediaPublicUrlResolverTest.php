<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\MediaContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\MediaContext\Infrastructure\Service\MediaPublicUrlResolver;

final class MediaPublicUrlResolverTest extends TestCase
{
    public function testItResolvesRelativeGalleryAndCoverKeysWithoutExposingTheFilesystemRoot(): void
    {
        $repository = $this->createMock(MediaRepositoryInterface::class);
        $repository->method('findByIds')->willReturnCallback(static fn (array $ids): array => array_map(
            static fn (int $id): Media => new Media(
                $id,
                'uuid-' . $id,
                'image.webp',
                str_repeat('a', 48) . '.webp',
                'image/webp',
                1,
                1,
                1,
                $id === 1 ? 'galleries/' . str_repeat('a', 48) . '.webp' : 'content/covers/' . str_repeat('b', 48) . '.webp',
            ),
            $ids,
        ));

        $urls = (new MediaPublicUrlResolver($repository))->resolveMany([1, 2]);

        self::assertSame('/uploads/galleries/' . str_repeat('a', 48) . '.webp', $urls[1]);
        self::assertSame('/uploads/content/covers/' . str_repeat('b', 48) . '.webp', $urls[2]);
        self::assertStringNotContainsString('/shared/storage', implode(' ', $urls));
    }
}
