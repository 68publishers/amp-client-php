<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use DateTimeImmutable;
use SixtyEightPublishers\AmpClient\Http\Cache\CachedResponse;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use SixtyEightPublishers\AmpClient\Http\Cache\InMemoryCacheStorage;
use SlopeIt\ClockMock\ClockMock;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class InMemoryCacheStorageTest extends TestCase
{
    public function testResponseShouldBeSavedAndReturned(): void
    {
        $storage = new InMemoryCacheStorage();

        $cacheKey = CacheKey::compute(['test' => 'a', 'test2' => 'b']);
        $response = new CachedResponse($cacheKey, (object) [], Expiration::create(60), null);

        $storage->save($response, Expiration::create(3600));

        Assert::same($response, $storage->get($cacheKey));
    }

    public function testCacheShouldReturnNullWhenResponseIsMissing(): void
    {
        $storage = new InMemoryCacheStorage();
        $cacheKey = CacheKey::compute(['test' => 'a', 'test2' => 'b']);

        Assert::null($storage->get($cacheKey));
    }

    public function testCacheShouldReturnNullWhenResponseIsExpired(): void
    {
        $storage = new InMemoryCacheStorage();

        $cacheKey = CacheKey::compute(['test' => 'a', 'test2' => 'b']);
        $response = new CachedResponse($cacheKey, (object) [], Expiration::create(30), null);

        $nowPlus50Seconds = new DateTimeImmutable('+50 seconds');
        $nowPlus60Seconds = new DateTimeImmutable('+60 seconds');
        $nowPlus61Seconds = new DateTimeImmutable('+61 seconds');

        $storage->save($response, Expiration::create(60));

        Assert::same($response, $storage->get($cacheKey));

        $responseAfter50Seconds = ClockMock::executeAtFrozenDateTime($nowPlus50Seconds, static fn () => $storage->get($cacheKey));
        $responseAfter60Seconds = ClockMock::executeAtFrozenDateTime($nowPlus60Seconds, static fn () => $storage->get($cacheKey));
        $responseAfter61Seconds = ClockMock::executeAtFrozenDateTime($nowPlus61Seconds, static fn () => $storage->get($cacheKey));

        Assert::same($response, $responseAfter50Seconds);
        Assert::same($response, $responseAfter60Seconds);
        Assert::null($responseAfter61Seconds);
    }

    public function testResponseShouldBeDeleted(): void
    {
        $storage = new InMemoryCacheStorage();

        $cacheKey1 = CacheKey::compute(['test' => 'a', 'test2' => 'b']);
        $response1 = new CachedResponse($cacheKey1, (object) [], Expiration::create(60), null);

        $cacheKey2 = CacheKey::compute(['test' => 'c', 'test2' => 'd']);
        $response2 = new CachedResponse($cacheKey2, (object) [], Expiration::create(60), null);

        $storage->save($response1, Expiration::create(3600));
        $storage->save($response2, Expiration::create(3600));

        $storage->delete($cacheKey1);

        Assert::null($storage->get($cacheKey1));
        Assert::same($response2, $storage->get($cacheKey2));
    }

    public function testStorageShouldBeCleared(): void
    {
        $storage = new InMemoryCacheStorage();

        $cacheKey1 = CacheKey::compute(['test' => 'a', 'test2' => 'b']);
        $response1 = new CachedResponse($cacheKey1, (object) [], Expiration::create(60), null);

        $cacheKey2 = CacheKey::compute(['test' => 'c', 'test2' => 'd']);
        $response2 = new CachedResponse($cacheKey2, (object) [], Expiration::create(60), null);

        $storage->save($response1, Expiration::create(3600));
        $storage->save($response2, Expiration::create(3600));

        $storage->clear();

        Assert::null($storage->get($cacheKey1));
        Assert::null($storage->get($cacheKey2));
    }
}

(new InMemoryCacheStorageTest())->run();
