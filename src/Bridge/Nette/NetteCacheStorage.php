<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\Log\LoggerInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\CachedResponse;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use Throwable;

final class NetteCacheStorage implements CacheStorageInterface
{
    private Cache $cache;

    private ?LoggerInterface $logger;

    public function __construct(Storage $storage, ?LoggerInterface $logger = null)
    {
        $this->cache = new Cache($storage, self::class);
        $this->logger = $logger;
    }

    public function get(CacheKey $key): ?CachedResponse
    {
        try {
            $response = $this->cache->load($key->getValue());

            if (!$response instanceof CachedResponse) {
                $this->delete($key);

                return null;
            }

            return $response;
        } catch (Throwable $e) {
            if (null !== $this->logger) {
                $this->logger->error('[AMP] Unable to load response from cache: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            return null;
        }
    }

    public function save(CachedResponse $response, Expiration $expiration): void
    {
        try {
            $this->cache->save($response->getKey()->getValue(), $response, [
                Cache::EXPIRE => $expiration->getValue(),
            ]);
        } catch (Throwable $e) {
            if (null !== $this->logger) {
                $this->logger->error('[AMP] Unable to save response to cache: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
    }

    public function delete(CacheKey $key): void
    {
        try {
            $this->cache->remove($key->getValue());
        } catch (Throwable $e) {
            if (null !== $this->logger) {
                $this->logger->error('[AMP] Unable to delete response from cache: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
    }

    public function clear(): void
    {
        try {
            $this->cache->clean([
                Cache::ALL,
            ]);
        } catch (Throwable $e) {
            if (null !== $this->logger) {
                $this->logger->error('[AMP] Unable to clear cache: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
    }
}
