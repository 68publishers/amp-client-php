<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControlHeader;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class CacheControlTest extends TestCase
{
    public function testObjectCreation(): void
    {
        $a = new CacheControl('+1 minute');
        $b = new CacheControl(60, 'max-age=300, must-revalidate');

        Assert::equal(Expiration::create('+1 minute'), $a->createExpiration());
        Assert::null($a->getCacheControlHeaderOverride());

        Assert::equal(Expiration::create(60), $b->createExpiration());
        Assert::equal(new CacheControlHeader(['max-age=300, must-revalidate']), $b->getCacheControlHeaderOverride());
    }
}

(new CacheControlTest())->run();
