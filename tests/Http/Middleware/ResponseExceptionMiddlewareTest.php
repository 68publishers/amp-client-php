<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Middleware;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use SixtyEightPublishers\AmpClient\Exception\AmpHttpExceptionInterface;
use SixtyEightPublishers\AmpClient\Exception\BadRequestException;
use SixtyEightPublishers\AmpClient\Exception\NotFoundException;
use SixtyEightPublishers\AmpClient\Exception\ServerErrorException;
use SixtyEightPublishers\AmpClient\Http\Middleware\ResponseExceptionMiddleware;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../../bootstrap.php';

final class ResponseExceptionMiddlewareTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        $middleware = new ResponseExceptionMiddleware();

        Assert::same('response_exception', $middleware->getName());
    }

    public function testPriorityShouldBeReturned(): void
    {
        $middleware = new ResponseExceptionMiddleware();

        Assert::same(90, $middleware->getPriority());
    }

    public function testResponseShouldBeReturnedOnNonErrorStatusCode(): void
    {
        $request = new Request('GET', 'https://example.com', [], '');
        $response = new Response(200, [], 'OK');
        $options = [];
        $next = static fn (): FulfilledPromise => new FulfilledPromise($response);

        $middleware = new ResponseExceptionMiddleware();
        $middlewareFunction = $middleware($next);

        $returnedResponse = $middlewareFunction($request, $options)->wait();

        Assert::same($response, $returnedResponse);
    }

    /**
     * @dataProvider dataProviderHttpResponseExceptionShouldBeThrown
     */
    public function testHttpResponseExceptionShouldBeThrown(
        int $statusCode,
        string $responseBody,
        string $exceptionClassname,
        string $exceptionMessage,
        bool $isJson
    ): void {
        $request = new Request('GET', 'https://example.com', [], '');
        $response = new Response($statusCode, $isJson ? ['Content-Type' => 'application/json'] : [], $responseBody);
        $next = static fn (): FulfilledPromise => new FulfilledPromise($response);

        $middleware = new ResponseExceptionMiddleware();
        $middlewareFunction = $middleware($next);

        $thrownException = Assert::exception(
            static fn () => $middlewareFunction($request, [])->wait(),
            $exceptionClassname,
        );

        assert($thrownException instanceof AmpHttpExceptionInterface);

        Assert::same($request, $thrownException->getRequest());
        Assert::same($response, $thrownException->getResponse());
        Assert::equal($exceptionMessage, $thrownException->getMessage());
    }

    public function dataProviderHttpResponseExceptionShouldBeThrown(): array
    {
        return [
            [400, '{"status":"error","data":{"code":400,"error":"Bad request 400!"}}', BadRequestException::class, 'Bad request 400!', true],
            [403, '{"status":"error","data":{"code":403,"error":"Bad request 403!"}}', BadRequestException::class, 'Bad request 403!', true],
            [404, '{"status":"error","data":{"code":404,"error":"Not found!"}}', NotFoundException::class, 'Not found!', true],
            [408, 'Timeout!', BadRequestException::class, 'Timeout!', false], // text response
            [500, '{"status":"error","message":"Server error 500!"}', ServerErrorException::class, 'Server error 500!', true],
            [503, '{"status":"error","message":"Server error 503!"}', ServerErrorException::class, 'Server error 503!', true],
            [504, 'Gateway timeout!', ServerErrorException::class, 'Gateway timeout!', false], // text response
            [504, '{"error":"Gateway timeout!', ServerErrorException::class, '{"error":"Gateway timeout!', true], // invalid json
        ];
    }
}

(new ResponseExceptionMiddlewareTest())->run();
