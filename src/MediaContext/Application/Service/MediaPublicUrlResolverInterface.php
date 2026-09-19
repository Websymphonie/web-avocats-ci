<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

interface MediaPublicUrlResolverInterface
{
    /**
     * @param list<int> $mediaIds
     * @return array<int, string>
     */
    public function resolveMany(array $mediaIds): array;
}
