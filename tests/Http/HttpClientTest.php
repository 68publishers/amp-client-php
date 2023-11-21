<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http;

use ArrayObject;
use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use SixtyEightPublishers\AmpClient\Exception\ResponseHydrationException;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControlHeader;
use SixtyEightPublishers\AmpClient\Http\Cache\CachedResponse;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\Etag;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use SixtyEightPublishers\AmpClient\Http\HttpClient;
use SixtyEightPublishers\AmpClient\Http\HttpRequest;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;
use SlopeIt\ClockMock\ClockMock;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class HttpClientTest extends TestCase
{
    public function testRequestWithoutCacheComponentsShouldBeSent(): void
    {
        $services = $this->buildHttpClient([
            new Response(200, [], '{"version":1}'),
            new Response(200, [], '{"version":2}'),
        ]);
        $client = $services['client'];
        $history = $services['history'];
        $cacheControl = $services['cacheControl'];
        $hydrator = $services['hydrator'];
        $expectedResult1 = new stdClass();
        $expectedResult2 = new stdClass();

        $cacheControl
            ->shouldReceive('getCacheControlHeaderOverride')
            ->twice()
            ->withNoArgs()
            ->andReturnNull();

        $hydrator
            ->shouldReceive('hydrate')
            ->once()
            ->with('stdClass', ['version' => 1])
            ->andReturn($expectedResult1)
            ->shouldReceive('hydrate')
            ->once()
            ->with('stdClass', ['version' => 2])
            ->andReturn($expectedResult2);

        $request = new HttpRequest(
            'GET',
            '/test',
            [
                'query' => [
                    'test' => '1',
                ],
            ],
        );

        $result1 = $client->request($request, 'stdClass');

        Assert::same($expectedResult1, $result1);
        Assert::same('GET', $history[0]['request']->getMethod());
        Assert::same('https://www.example.com/test?test=1', (string) $history[0]['request']->getUri());

        $result2 = $client->request($request, 'stdClass');

        Assert::same($expectedResult2, $result2);
        Assert::same('GET', $history[1]['request']->getMethod());
        Assert::same('https://www.example.com/test?test=1', (string) $history[1]['request']->getUri());
    }

    public function testExceptionShouldBeThrownOnInvalidResponseBody(): void
    {
        $services = $this->buildHttpClient([
            new Response(200, [], '{"version":1'), // invalid JSON
        ]);
        $client = $services['client'];
        $cacheControl = $services['cacheControl'];

        $cacheControl
            ->shouldReceive('getCacheControlHeaderOverride')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $request = new HttpRequest(
            'GET',
            '/test',
            [
                'query' => [
                    'test' => '1',
                ],
            ],
        );

        Assert::exception(
            static fn () => $client->request($request, 'stdClass'),
            ResponseHydrationException::class,
            'Response body is probably malformed.%A?%',
        );
    }

    public function testResponseShouldBeReturnedFromCacheIfFresh(): void
    {
        $services = $this->buildHttpClient([]);
        $client = $services['client'];
        $cache = $services['cache'];

        $expectedResult = new stdClass();
        $cachedResponse = Mockery::mock(CachedResponse::class);

        $cache
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::type(CacheKey::class))
            ->andReturn($cachedResponse);

        $cachedResponse
            ->shouldReceive('isFresh')
            ->andReturn(true)
            ->shouldReceive('getResponse')
            ->andReturn($expectedResult);

        $request = new HttpRequest(
            'GET',
            '/test',
            [],
            [
                'cacheComponent1' => 'test',
            ],
        );

        $result = $client->request($request, 'stdClass');

        Assert::same($expectedResult, $result);
    }

    /**
     * @dataProvider cachingParametersDataProvider
     */
    public function testResponseShouldOrShouldNotBeCachedWhenThereIsNotCacheHit(
        array $responseHeaders,
        bool $shouldBeStored,
        int $maxAge,
        ?string $etag,
        ?string $cacheControlHeaderOverride
    ): void {
        ClockMock::executeAtFrozenDateTime(new DateTimeImmutable('now'), function () use ($responseHeaders, $shouldBeStored, $maxAge, $etag, $cacheControlHeaderOverride): void {
            $response = new Response(200, $responseHeaders, '{"version":1}');

            $services = $this->buildHttpClient([$response]);
            $client = $services['client'];
            $history = $services['history'];
            $cache = $services['cache'];
            $cacheControl = $services['cacheControl'];
            $hydrator = $services['hydrator'];

            $expectedResult = new stdClass();

            $hydrator
                ->shouldReceive('hydrate')
                ->once()
                ->with('stdClass', ['version' => 1])
                ->andReturn($expectedResult);

            $cacheControl
                ->shouldReceive('getCacheControlHeaderOverride')
                ->once()
                ->withNoArgs()
                ->andReturn(null !== $cacheControlHeaderOverride ? new CacheControlHeader([$cacheControlHeaderOverride]) : null);

            $cache
                ->shouldReceive('get')
                ->once()
                ->with(Mockery::type(CacheKey::class))
                ->andReturnNull();

            if ($shouldBeStored) {
                $cacheControl
                    ->shouldReceive('createExpiration')
                    ->withNoArgs()
                    ->andReturn(Expiration::create(3600));

                $cache
                    ->shouldReceive('save')
                    ->with(Mockery::type(CachedResponse::class), Mockery::type(Expiration::class))
                    ->andReturnUsing(function (CachedResponse $cachedResponse) use ($expectedResult, $maxAge, $etag) {
                        Assert::same($expectedResult, $cachedResponse->getResponse());
                        Assert::equal(Expiration::create($maxAge), $cachedResponse->getMaxAge());
                        Assert::equal(null !== $etag ? new Etag($etag) : null, $cachedResponse->getEtag());

                        return null;
                    });
            }

            $request = new HttpRequest(
                'GET',
                '/test',
                [
                    'query' => [
                        'test' => '1',
                    ],
                ],
                [
                    'cacheComponent1' => 'test',
                ],
            );

            $result = $client->request($request, 'stdClass');

            Assert::same($expectedResult, $result);
            Assert::same('GET', $history[0]['request']->getMethod());
            Assert::same('https://www.example.com/test?test=1', (string) $history[0]['request']->getUri());
        });
    }

    /**
     * @dataProvider cachingParametersDataProvider
     */
    public function testResponseShouldBeCachedWhenResponseIsNotModified(
        array $responseHeaders,
        bool $shouldBeStored,
        int $maxAge,
        ?string $etag,
        ?string $cacheControlHeaderOverride
    ): void {
        ClockMock::executeAtFrozenDateTime(new DateTimeImmutable('now'), function () use ($responseHeaders, $shouldBeStored, $maxAge, $etag, $cacheControlHeaderOverride): void {
            $response = new Response(304, $responseHeaders);

            $services = $this->buildHttpClient([$response]);
            $client = $services['client'];
            $history = $services['history'];
            $cache = $services['cache'];
            $cacheControl = $services['cacheControl'];

            $expectedResult = new stdClass();
            $cachedResponse = Mockery::mock(CachedResponse::class);

            $cachedResponse
                ->shouldReceive('isFresh')
                ->andReturn(false)
                ->shouldReceive('getResponse')
                ->andReturn($expectedResult)
                ->shouldReceive('getEtag')
                ->andReturn(new Etag('1234'));

            $cacheControl
                ->shouldReceive('getCacheControlHeaderOverride')
                ->once()
                ->withNoArgs()
                ->andReturn(null !== $cacheControlHeaderOverride ? new CacheControlHeader([$cacheControlHeaderOverride]) : null);

            $cache
                ->shouldReceive('get')
                ->once()
                ->with(Mockery::type(CacheKey::class))
                ->andReturn($cachedResponse);

            if ($shouldBeStored) {
                $cacheControl
                    ->shouldReceive('createExpiration')
                    ->withNoArgs()
                    ->andReturn(Expiration::create(3600));

                $cache
                    ->shouldReceive('save')
                    ->with(Mockery::type(CachedResponse::class), Mockery::type(Expiration::class))
                    ->andReturnUsing(function (CachedResponse $cachedResponse) use ($expectedResult, $maxAge, $etag) {
                        Assert::same($expectedResult, $cachedResponse->getResponse());
                        Assert::equal(Expiration::create($maxAge), $cachedResponse->getMaxAge());
                        Assert::equal(null !== $etag ? new Etag($etag) : null, $cachedResponse->getEtag());

                        return null;
                    });
            } else {
                $cache
                    ->shouldReceive('delete')
                    ->with(Mockery::type(CacheKey::class))
                    ->andReturns();
            }

            $request = new HttpRequest(
                'GET',
                '/test',
                [
                    'query' => [
                        'test' => '1',
                    ],
                ],
                [
                    'cacheComponent1' => 'test',
                ],
            );

            $result = $client->request($request, 'stdClass');

            Assert::same($expectedResult, $result);
            Assert::same('GET', $history[0]['request']->getMethod());
            Assert::same('https://www.example.com/test?test=1', (string) $history[0]['request']->getUri());
            Assert::same(['1234'], $history[0]['request']->getHeader('If-None-Match'));
        });
    }

    public function cachingParametersDataProvider(): array
    {
        # 0 => array $responseHeaders
        # 1 => bool $shouldBeStored
        # 2 => int $maxAge
        # 3 => ?string $etag
        # 4 => ?string $cacheControlHeaderOverride
        return [
            [
                0 => [],
                1 => false,
                2 => 0,
                3 => null,
                4 => null,
            ],
            [
                0 => ['ETag' => '123456'],
                1 => false,
                2 => 0,
                3 => '123456',
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'max-age=100'],
                1 => true,
                2 => 100,
                3 => null,
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'max-age=100', 'ETag' => '123456'],
                1 => true,
                2 => 100,
                3 => '123456',
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'no-cache, max-age=100'],
                1 => true,
                2 => 0,
                3 => null,
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'no-store, max-age=100'],
                1 => false,
                2 => 0,
                3 => null,
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'max-age=100, s-maxage=50'],
                1 => true,
                2 => 50,
                3 => null,
                4 => null,
            ],
            [
                0 => ['Cache-Control' => 'max-age=100', 'ETag' => '123456'],
                1 => false,
                2 => 0,
                3 => null,
                4 => 'no-store',
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @param array<int, ResponseInterface> $responses
     *
     * @return array{
     *     client: HttpClient,
     *     history: ArrayObject,
     *     hydrator: ResponseHydratorInterface|MockInterface,
     *     cache: CacheStorageInterface|MockInterface,
     *     cacheControl: CacheControl|MockInterface,
     * }
     */
    private function buildHttpClient(array $responses): array
    {
        $responseHydrator = Mockery::mock(ResponseHydratorInterface::class);
        $cacheStorage = Mockery::mock(CacheStorageInterface::class);
        $cacheControl = Mockery::mock(CacheControl::class);

        $guzzleClientHandler = HandlerStack::create(
            new MockHandler($responses),
        );
        $history = new ArrayObject([]);

        $guzzleClientHandler->push(Middleware::history($history), 'history');

        $guzzleClient = new GuzzleClient([
            'handler' => $guzzleClientHandler,
        ]);

        $client = new HttpClient('https://www.example.com/', $guzzleClient, $responseHydrator, $cacheStorage, $cacheControl);

        return [
            'client' => $client,
            'history' => $history,
            'hydrator' => $responseHydrator,
            'cache' => $cacheStorage,
            'cacheControl' => $cacheControl,
        ];
    }
}

(new HttpClientTest())->run();
