<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

interface CacheStorageInterface
{
    public function get(CacheKey $key): ?CachedResponse;

    public function save(CachedResponse $response, Expiration $expiration): void;

    public function delete(CacheKey $key): void;

    public function clear(): void;
}
