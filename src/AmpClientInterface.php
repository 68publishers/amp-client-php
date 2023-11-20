<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient;

use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;

interface AmpClientInterface
{
    public function getConfig(): ClientConfig;

    public function withConfig(ClientConfig $config): self;

    public function getCacheStorage(): CacheStorageInterface;

    public function withCacheStorage(CacheStorageInterface $cacheStorage): self;

    /**
     * @throws AmpExceptionInterface
     */
    public function fetchBanners(BannersRequest $request): BannersResponse;
}
