<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use SixtyEightPublishers\AmpClient\Http\Cache\CachedResponse;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class NoCacheStorageTest extends TestCase
{
    public function testNothingIsCached(): void
    {
        $storage = new NoCacheStorage();

        $cacheKey = CacheKey::compute(['test' => 'a', 'test2' => 'b']);
        $response = new CachedResponse($cacheKey, (object) [], Expiration::create(3600), null);

        $storage->save($response, Expiration::create(3600));

        Assert::null($storage->get($cacheKey));

        Assert::noError(static function () use ($storage, $cacheKey) {
            $storage->delete($cacheKey);
            $storage->clear();
        });
    }
}

(new NoCacheStorageTest())->run();
