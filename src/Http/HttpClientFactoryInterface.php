<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;

interface HttpClientFactoryInterface
{
    public function create(
        string $baseUrl,
        Middlewares $middlewares,
        CacheStorageInterface $cacheStorage,
        CacheControl $cacheControl
    ): HttpClientInterface;
}
