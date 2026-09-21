<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Service;

use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;

final readonly class MediaPublicUrlResolver implements MediaPublicUrlResolverInterface
{
    public function __construct(private MediaRepositoryInterface $repository) {}
    /**
     * @param list<int> $mediaIds
     * @return array<int, string>
     */
    public function resolveMany(array $mediaIds): array
    {
        $urls = [];
        foreach ($this->repository->findByIds(array_values(array_unique($mediaIds))) as $media) {
            $storagePath = ltrim($media->storagePath, '/');
            $urls[$media->id] = str_starts_with($storagePath, 'uploads/') ? '/' . $storagePath : '/uploads/' . $storagePath;
        }
        return $urls;
    }
}
