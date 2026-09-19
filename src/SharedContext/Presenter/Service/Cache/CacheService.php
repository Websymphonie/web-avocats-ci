<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Cache;

use Psr\Cache\InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

readonly class CacheService implements CacheServiceInterface
{
    public function __construct(
        private CacheInterface $cache,
        private int            $defaultTtl = 3600
    )
    {
    }

    /**
     * @template T
     * @param string $key
     * @param callable():T $callback
     * @param string[] $tags
     * @param int|null $ttl
     * @return T
     * @throws InvalidArgumentException
     */
    public function getCache(
        string   $key,
        callable $callback,
        array    $tags = [],
        ?int     $ttl = null
    ): mixed
    {
        return $this->cache->get($this->normalizeKey($key), function (ItemInterface $item) use ($callback, $tags, $ttl) {
            $item->expiresAfter($ttl ?? $this->defaultTtl);

            if ($this->cache instanceof TagAwareAdapterInterface && $tags !== []) {
                $item->tag($tags);
            }

            return $callback();
        });
    }

    private function normalizeKey(string $key): string
    {
        return 'excelsior_' . hash('sha256', $key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteCache(string $key): void
    {
        $this->cache->delete($this->normalizeKey($key));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidateTag(string $tag): void
    {
        if ($this->cache instanceof TagAwareAdapterInterface) {
            $this->cache->invalidateTags([$tag]);
        }
    }

    public function clearAllCache(): void
    {
        if ($this->cache instanceof CacheItemPoolInterface) {
            $this->cache->clear();
        }
    }
}
