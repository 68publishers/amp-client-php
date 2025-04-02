<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Closing;

use SixtyEightPublishers\AmpClient\Closing\EntryKey;
use SixtyEightPublishers\AmpClient\Closing\NullClosedEntriesStore;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class NullClosedEntriesStoreTest extends TestCase
{
    public function testShouldAlwaysReturnFalse(): void
    {
        $store = new NullClosedEntriesStore();

        Assert::false($store->isClosed(EntryKey::position('foo'), 0));
        Assert::false($store->isClosed(EntryKey::banner('foo', '1'), 0));
    }
}

(new NullClosedEntriesStoreTest())->run();
