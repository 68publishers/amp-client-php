<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use DateTimeImmutable;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use SlopeIt\ClockMock\ClockMock;
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

    public function testExpirationFromString(): void
    {
        $nowPlus50Seconds = new DateTimeImmutable('+50 seconds');
        $nowPlus60Seconds = new DateTimeImmutable('+60 seconds');
        $nowPlus61Seconds = new DateTimeImmutable('+61 seconds');

        $expiration = Expiration::create('+60 seconds');

        Assert::same(time() + 60, $expiration->getValue());

        Assert::false($expiration->isExpired());
        Assert::true($expiration->isFresh());

        [$isExpiredAfter50Seconds, $isFreshAfter50Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus50Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::false($isExpiredAfter50Seconds);
        Assert::true($isFreshAfter50Seconds);

        // still fresh
        [$isExpiredAfter60Seconds, $isFreshAfter60Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus60Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::false($isExpiredAfter60Seconds);
        Assert::true($isFreshAfter60Seconds);

        // not fresh
        [$isExpiredAfter61Seconds, $isFreshAfter61Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus61Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::true($isExpiredAfter61Seconds);
        Assert::false($isFreshAfter61Seconds);
    }

    public function testExpirationFromInteger(): void
    {
        $nowPlus50Seconds = new DateTimeImmutable('+50 seconds');
        $nowPlus60Seconds = new DateTimeImmutable('+60 seconds');
        $nowPlus61Seconds = new DateTimeImmutable('+61 seconds');

        $expiration = Expiration::create(60);

        Assert::same(time() + 60, $expiration->getValue());

        Assert::false($expiration->isExpired());
        Assert::true($expiration->isFresh());

        [$isExpiredAfter50Seconds, $isFreshAfter50Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus50Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::false($isExpiredAfter50Seconds);
        Assert::true($isFreshAfter50Seconds);

        // still fresh
        [$isExpiredAfter60Seconds, $isFreshAfter60Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus60Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::false($isExpiredAfter60Seconds);
        Assert::true($isFreshAfter60Seconds);

        // not fresh
        [$isExpiredAfter61Seconds, $isFreshAfter61Seconds] = ClockMock::executeAtFrozenDateTime($nowPlus61Seconds, static fn () => [$expiration->isExpired(), $expiration->isFresh()]);

        Assert::true($isExpiredAfter61Seconds);
        Assert::false($isFreshAfter61Seconds);
    }
}

(new ExpirationTest())->run();
