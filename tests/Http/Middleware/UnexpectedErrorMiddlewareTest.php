<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Middleware;

use GuzzleHttp\Psr7\Request;
use RuntimeException;
use SixtyEightPublishers\AmpClient\Exception\UnexpectedErrorException;
use SixtyEightPublishers\AmpClient\Http\Middleware\UnexpectedErrorMiddleware;
use SixtyEightPublishers\AmpClient\Tests\Exception\AmpExceptionFixture;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class UnexpectedErrorMiddlewareTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        $middleware = new UnexpectedErrorMiddleware();

        Assert::same('unexpected_error', $middleware->getName());
    }

    public function testPriorityShouldBeReturned(): void
    {
        $middleware = new UnexpectedErrorMiddleware();

        Assert::same(100, $middleware->getPriority());
    }

    public function testAmpExceptionShouldBeThrown(): void
    {
        $request = new Request('GET', 'https://example.com', [], '');
        $options = [];
        $next = static function (): void {
            throw new AmpExceptionFixture('Test amp exception.');
        };

        $middleware = new UnexpectedErrorMiddleware();
        $middlewareFunction = $middleware($next);

        $thrownException = Assert::exception(
            static fn () => $middlewareFunction($request, $options),
            AmpExceptionFixture::class,
            'Test amp exception.',
        );

        Assert::null($thrownException->getPrevious());
    }

    public function testUnexpectedErrorExceptionShouldBeThrown(): void
    {
        $originalException = new RuntimeException('Test runtime exception.');
        $request = new Request('GET', 'https://example.com', [], '');
        $options = [];
        $next = static function () use ($originalException): void {
            throw $originalException;
        };

        $middleware = new UnexpectedErrorMiddleware();
        $middlewareFunction = $middleware($next);

        $thrownException = Assert::exception(
            static fn () => $middlewareFunction($request, $options),
            UnexpectedErrorException::class,
            'Client thrown an unexpected exception: Test runtime exception.',
        );

        Assert::same($originalException, $thrownException->getPrevious());
    }
}

(new UnexpectedErrorMiddlewareTest())->run();
