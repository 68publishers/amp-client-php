<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Middleware;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use SixtyEightPublishers\AmpClient\Http\Middleware\XAmpOriginHeaderMiddleware;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class XAmpOriginHeaderMiddlewareTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        $middleware = new XAmpOriginHeaderMiddleware('https://test.example.com');

        Assert::same('x_amp_origin_header', $middleware->getName());
    }

    public function testPriorityShouldBeReturned(): void
    {
        $middleware = new XAmpOriginHeaderMiddleware('https://test.example.com');

        Assert::same(80, $middleware->getPriority());
    }

    public function testXAmpOriginHeaderShouldBeAdded(): void
    {
        $request = new Request('GET', 'https://example.com', [], '');
        $next = static fn (RequestInterface $request): FulfilledPromise => new FulfilledPromise($request);

        $middleware = new XAmpOriginHeaderMiddleware('https://test.example.com');
        $middlewareFunction = $middleware($next);
        $returnedRequest = $middlewareFunction($request, [])->wait();

        Assert::same(['https://test.example.com'], $returnedRequest->getHeader('x-amp-origin'));
    }
}

(new XAmpOriginHeaderMiddlewareTest())->run();
