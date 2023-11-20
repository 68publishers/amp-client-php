<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class CacheKeyTest extends TestCase
{
    public function testKeyComputing(): void
    {
        // just for possible future breaks
        $a = CacheKey::compute([
            '__url' => 'https://www.example.com/api/test',
            '__version' => 1,
            'query' => [
                'foo' => 'bar',
                'test' => '1',
            ],
        ]);

        Assert::same('5f-tX8P4AcvzMTW8RAeB/IfbPkhmtA', $a->getValue());
    }
}

(new CacheKeyTest())->run();
