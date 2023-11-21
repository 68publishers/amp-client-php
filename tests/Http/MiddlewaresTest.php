<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http;

use SixtyEightPublishers\AmpClient\Http\Middlewares;
use SixtyEightPublishers\AmpClient\Tests\Http\Middleware\MiddlewareFixture;
use Tester\Assert;
use Tester\TestCase;
use function iterator_to_array;

require __DIR__ . '/../bootstrap.php';

final class MiddlewaresTest extends TestCase
{
    public function testMiddlewaresAreSorted(): void
    {
        $middleware1 = new MiddlewareFixture('1', 100);
        $middleware2 = new MiddlewareFixture('2', 200);
        $middleware3 = new MiddlewareFixture('3', 300);

        $middlewares = new Middlewares([
            $middleware1,
            $middleware2,
            $middleware3,
        ]);

        Assert::same([$middleware3, $middleware2, $middleware1], iterator_to_array($middlewares->getIterator()));
    }

    public function testMiddlewaresImmutability(): void
    {
        $middleware1 = new MiddlewareFixture('1', 100);
        $middleware2 = new MiddlewareFixture('2', 200);
        $middleware3 = new MiddlewareFixture('3', 300);

        $middlewares = new Middlewares([
            $middleware1,
            $middleware2,
        ]);

        $modified = $middlewares->with($middleware3);

        Assert::same([$middleware2, $middleware1], iterator_to_array($middlewares->getIterator()));
        Assert::same([$middleware3, $middleware2, $middleware1], iterator_to_array($modified->getIterator()));
    }
}

(new MiddlewaresTest())->run();
