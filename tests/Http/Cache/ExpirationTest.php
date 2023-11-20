<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use Tester\Assert;
use Tester\TestCase;
use function time;

require __DIR__ . '/../../bootstrap.php';

final class ExpirationTest extends TestCase
{
    public function testZeroExpirationFromStringShouldBeDirectlyExpired(): void
    {
        $expiration = Expiration::create('0 seconds');

        Assert::same(time() - 1, $expiration->getValue());
        Assert::true($expiration->isExpired());
        Assert::false($expiration->isFresh());
    }

    public function testZeroExpirationFromIntegerShouldBeDirectlyExpired(): void
    {
        $expiration = Expiration::create(0);

        Assert::same(time() - 1, $expiration->getValue());
        Assert::true($expiration->isExpired());
        Assert::false($expiration->isFresh());
    }

    public function testFutureExpirationFromStringShouldBeFresh(): void
    {
        $expiration = Expiration::create('+60 seconds');

        Assert::same(time() + 60, $expiration->getValue());
        Assert::false($expiration->isExpired());
        Assert::true($expiration->isFresh());
    }

    public function testFutureExpirationFromIntegerShouldBeFresh(): void
    {
        $expiration = Expiration::create(60);

        Assert::same(time() + 60, $expiration->getValue());
        Assert::false($expiration->isExpired());
        Assert::true($expiration->isFresh());
    }

    public function testPastExpirationShouldBeExpired(): void
    {
        $expiration = new Expiration(time() - 1);

        Assert::true($expiration->isExpired());
        Assert::false($expiration->isFresh());
    }
}

(new ExpirationTest())->run();
