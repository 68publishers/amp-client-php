<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

final class NoCacheStorage implements CacheStorageInterface
{
    public function get(CacheKey $key): ?CachedResponse
    {
        return null;
    }

    public function save(CachedResponse $response, Expiration $expiration): void
    {
    }

    public function delete(CacheKey $key): void
    {
    }

    public function clear(): void
    {
    }
}
