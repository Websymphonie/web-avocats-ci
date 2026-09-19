<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Cache;

interface CacheServiceInterface
{
    /**
     * @template T
     * @param callable():T $callback
     * @param list<string> $tags
     * @return T
     */
    public function getCache(string $key, callable $callback, array $tags = [], ?int $ttl = null): mixed;

    public function deleteCache(string $key): void;

    public function clearAllCache(): void;

    public function invalidateTag(string $tag): void;
}
