<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests;

use Closure;
use Mockery;
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactoryInterface;
use SixtyEightPublishers\AmpClient\Http\HttpClientInterface;
use SixtyEightPublishers\AmpClient\Http\HttpRequest;
use SixtyEightPublishers\AmpClient\Http\Middleware\ResponseExceptionMiddleware;
use SixtyEightPublishers\AmpClient\Http\Middleware\UnexpectedErrorMiddleware;
use SixtyEightPublishers\AmpClient\Http\Middleware\XAmpOriginHeaderMiddleware;
use SixtyEightPublishers\AmpClient\Http\Middlewares;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use stdClass;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/bootstrap.php';

final class AmpClientTest extends TestCase
{
    public function testDefaultClientShouldBeCreated(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $client = AmpClient::create($config);

        Assert::same($config, $client->getConfig());
        Assert::type(NoCacheStorage::class, $client->getCacheStorage());

        call_user_func(Closure::bind(static function () use ($client) {
            Assert::equal(
                new HttpClientFactory(
                    new ResponseHydrator([
                        new BannersResponseHydratorHandler(),
                    ]),
                    [],
                ),
                $client->httpClientFactory,
            );
        }, null, AmpClient::class));

        $this->assertHttpClientIsNull($client);
    }

    public function testClientImmutability(): void
    {
        $config1 = ClientConfig::create('https://www.example.com', 'test');
        $cacheStorage1 = Mockery::mock(CacheStorageInterface::class);

        $client1 = new AmpClient(
            $config1,
            Mockery::mock(HttpClientFactoryInterface::class),
            $cacheStorage1,
        );

        $config2 = $config1->withChannel('demo');
        $cacheStorage2 = Mockery::mock(CacheStorageInterface::class);

        $client2 = $client1->withConfig($config2);
        $client3 = $client2->withCacheStorage($cacheStorage2);

        Assert::notSame($client2, $client1);
        Assert::notSame($client3, $client2);

        Assert::same($config1, $client1->getConfig());
        Assert::same($cacheStorage1, $client1->getCacheStorage());

        Assert::same($config2, $client2->getConfig());
        Assert::same($cacheStorage1, $client2->getCacheStorage());

        Assert::same($config2, $client3->getConfig());
        Assert::same($cacheStorage2, $client3->getCacheStorage());
    }

    public function testEmptyBannersResourceShouldBeReturnedWhenEmptyBannersRequestPassed(): void
    {
        $client = new AmpClient(
            ClientConfig::create('https://www.example.com', 'test'),
            Mockery::mock(HttpClientFactoryInterface::class),
            Mockery::mock(CacheStorageInterface::class),
        );

        $request = new BannersRequest([]);

        Assert::equal(new BannersResponse([]), $client->fetchBanners($request));
    }

    /**
     * @dataProvider bannersFetchParametersDataProvider
     */
    public function testBannersFetching(
        ClientConfig $config,
        string $expectedBaseUrl,
        Middlewares $expectedMiddlewares,
        CacheControl $expectedCacheControl,
        BannersRequest $bannersRequest,
        HttpRequest $expectedHttpRequest
    ): void {
        $httpClientFactory = Mockery::mock(HttpClientFactoryInterface::class);
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $cacheStorage = Mockery::mock(CacheStorageInterface::class);

        $client = new AmpClient($config, $httpClientFactory, $cacheStorage);

        $httpClientFactory
            ->shouldReceive('create')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type(Middlewares::class),
                Mockery::type(CacheStorageInterface::class),
                Mockery::type(CacheControl::class),
            )
            ->andReturnUsing(static function (string $baseUrl, Middlewares $middlewares, CacheStorageInterface $passedCacheStorage, CacheControl $cacheControl) use ($expectedBaseUrl, $expectedMiddlewares, $cacheStorage, $expectedCacheControl, $httpClient): HttpClientInterface {
                Assert::same($expectedBaseUrl, $baseUrl);
                Assert::equal($expectedMiddlewares, $middlewares);
                Assert::same($cacheStorage, $passedCacheStorage);
                Assert::equal($expectedCacheControl, $cacheControl);

                return $httpClient;
            });

        $httpClient
            ->shouldReceive('request')
            ->with(Mockery::type(HttpRequest::class), BannersResponse::class)
            ->andReturnUsing(static function (HttpRequest $request) use ($expectedHttpRequest): BannersResponse {
                Assert::equal($expectedHttpRequest, $request);

                return new BannersResponse([]);
            });

        Assert::noError(static fn () => $client->fetchBanners($bannersRequest));
    }

    public function bannersFetchParametersDataProvider(): array
    {
        # 0 => ClientConfig $config
        # 1 => string $expectedBaseUrl
        # 2 => Middlewares $expectedMiddlewares
        # 3 => CacheControl $expectedCacheControl
        # 4 => BannersRequest $bannersRequest
        # 5 => HttpRequest $expectedHttpRequest

        return [
            'GET, [Request]: single position, no resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{}}',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'POST, [Request]: single position, no resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{}}}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'GET, [Request]: multiple positions, one with resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top', [
                        new BannerResource('first', 'a'),
                        new BannerResource('second', ['a', 'b']),
                    ]),
                    new Position('homepage.bottom'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{"first":["a"],"second":["a","b"]},"homepage.bottom":{}}',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => [
                                'first' => ['a'],
                                'second' => ['a', 'b'],
                            ],
                            'homepage.bottom' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'POST, [Request]: multiple positions, one with resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top', [
                        new BannerResource('first', 'a'),
                        new BannerResource('second', ['a', 'b']),
                    ]),
                    new Position('homepage.bottom'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{"first":["a"],"second":["a","b"]},"homepage.bottom":{}}}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => [
                                'first' => ['a'],
                                'second' => ['a', 'b'],
                            ],
                            'homepage.bottom' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'GET, [Request]: multiple positions, one with resources, [Config]: default resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withDefaultResources([
                        new BannerResource('first', ['a', 'c']),
                        new BannerResource('third', ['a']),
                    ]),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top', [
                        new BannerResource('first', 'a'),
                        new BannerResource('second', ['a', 'b']),
                    ]),
                    new Position('homepage.bottom'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{"first":["a","c"],"second":["a","b"],"third":["a"]},"homepage.bottom":{"first":["a","c"],"third":["a"]}}',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => [
                                'first' => ['a', 'c'],
                                'second' => ['a', 'b'],
                                'third' => ['a'],
                            ],
                            'homepage.bottom' => [
                                'first' => ['a', 'c'],
                                'third' => ['a'],
                            ],
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'POST, [Request]: multiple positions, one with resources, [Config]: default resources' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST')
                    ->withDefaultResources([
                        new BannerResource('first', ['a', 'c']),
                        new BannerResource('third', ['a']),
                    ]),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top', [
                        new BannerResource('first', 'a'),
                        new BannerResource('second', ['a', 'b']),
                    ]),
                    new Position('homepage.bottom'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{"first":["a","c"],"second":["a","b"],"third":["a"]},"homepage.bottom":{"first":["a","c"],"third":["a"]}}}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => [
                                'first' => ['a', 'c'],
                                'second' => ['a', 'b'],
                                'third' => ['a'],
                            ],
                            'homepage.bottom' => [
                                'first' => ['a', 'c'],
                                'third' => ['a'],
                            ],
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'GET, [Request]: single position, no resources, [Config]: origin' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withOrigin('https://example.io'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware(), new XAmpOriginHeaderMiddleware('https://example.io')]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{}}',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'POST, [Request]: single position, no resources, [Config]: origin' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST')
                    ->withOrigin('https://example.io'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware(), new XAmpOriginHeaderMiddleware('https://example.io')]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{}}}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'GET, [Request]: single position, no resources, [Config]: locale' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withLocale('en'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{}}',
                            'locale' => 'en',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => 'en',
                    ],
                ),
            ],
            'POST, [Request]: single position, no resources, [Config]: locale' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST')
                    ->withLocale('en'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{}},"locale":"en"}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => 'en',
                    ],
                ),
            ],
            'GET, [Request]: single position, no resources, locale, [Config]: locale' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withLocale('en'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ], 'cs'),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{}}',
                            'locale' => 'cs',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => 'cs',
                    ],
                ),
            ],
            'POST, [Request]: single position, no resources, locale, [Config]: locale' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST')
                    ->withLocale('en'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(0, null),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ], 'cs'),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{}},"locale":"cs"}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => 'cs',
                    ],
                ),
            ],
            'GET, [Request]: single position, no resources, [Config]: cache control' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withCacheExpiration(3600)
                    ->withCacheControlHeaderOverride('no-cache, max-age=200'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(3600, 'no-cache, max-age=200'),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'GET',
                    'content/test',
                    [
                        'query' => [
                            'query' => '{"homepage.top":{}}',
                        ],
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
            'POST, [Request]: single position, no resources, [Config]: cache control' => [
                0 => ClientConfig::create('https://www.example.com', 'test')
                    ->withMethod('POST')
                    ->withCacheExpiration(3600)
                    ->withCacheControlHeaderOverride('no-cache, max-age=200'),
                1 => 'https://www.example.com/api/v1',
                2 => new Middlewares([new UnexpectedErrorMiddleware(), new ResponseExceptionMiddleware()]),
                3 => new CacheControl(3600, 'no-cache, max-age=200'),
                4 => new BannersRequest([
                    new Position('homepage.top'),
                ]),
                5 => new HttpRequest(
                    'POST',
                    'content/test',
                    [
                        'body' => '{"query":{"homepage.top":{}}}',
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    ],
                    [
                        'query' => [
                            'homepage.top' => new stdClass(),
                        ],
                        'locale' => null,
                    ],
                ),
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function assertHttpClientIsNull(AmpClient $ampClient): void
    {
        call_user_func(Closure::bind(static function () use ($ampClient) {
            Assert::null($ampClient->httpClient);
        }, null, AmpClient::class));
    }
}

(new AmpClientTest())->run();
