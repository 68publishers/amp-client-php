<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Closing;

use Hamcrest\Matchers;
use Mockery;
use SixtyEightPublishers\AmpClient\Closing\ClosedEntriesStoreInterface;
use SixtyEightPublishers\AmpClient\Closing\ClosingManager;
use SixtyEightPublishers\AmpClient\Closing\EntryKey;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ClosingManagerTest extends TestCase
{
    public function testStoreShouldBeCalledOnIsBannerClosed(): void
    {
        $store = Mockery::mock(ClosedEntriesStoreInterface::class);
        $manager = new ClosingManager($store);

        $store->shouldReceive('isClosed')
            ->once()
            ->with(Matchers::equalTo(EntryKey::banner('foo', '1')), 0)
            ->andReturn(true);

        Assert::true($manager->isBannerClosed('foo', '1', 0));
    }

    public function testStoreShouldBeCalledOnIsPositionClosed(): void
    {
        $store = Mockery::mock(ClosedEntriesStoreInterface::class);
        $manager = new ClosingManager($store);

        $store->shouldReceive('isClosed')
            ->once()
            ->with(Matchers::equalTo(EntryKey::position('foo')), 0)
            ->andReturn(true);

        Assert::true($manager->isPositionClosed('foo', 0));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ClosingManagerTest())->run();
