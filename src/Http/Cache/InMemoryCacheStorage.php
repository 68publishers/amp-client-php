<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

final class InMemoryCacheStorage implements CacheStorageInterface
{
    /** @var array<string, array{0: CachedResponse, 1: Expiration}> */
    private array $cache = [];

    public function get(CacheKey $key): ?CachedResponse
    {
        if (!isset($this->cache[$key->getValue()])) {
            return null;
        }

        [$response, $expiration] = $this->cache[$key->getValue()];

        if ($expiration->isFresh()) {
            return $response;
        }

        $this->delete($key);

        return null;
    }

    public function save(CachedResponse $response, Expiration $expiration): void
    {
        $this->cache[$response->getKey()->getValue()] = [$response, $expiration];
    }

    public function delete(CacheKey $key): void
    {
        if (isset($this->cache[$key->getValue()])) {
            unset($this->cache[$key->getValue()]);
        }
    }

    public function clear(): void
    {
        $this->cache = [];
    }
}
