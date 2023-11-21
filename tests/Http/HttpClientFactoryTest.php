<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Utils;
use Mockery;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\HttpClient;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\Middlewares;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;
use SixtyEightPublishers\AmpClient\Tests\Http\Middleware\MiddlewareFixture;
use Tester\Assert;
use Tester\TestCase;
use function assert;
use function call_user_func;

require __DIR__ . '/../bootstrap.php';

final class HttpClientFactoryTest extends TestCase
{
    public function testClientShouldBeCreatedAndProperlyConfigured(): void
    {
        $baseUrl = 'https://www.example.com/';
        $responseHydrator = Mockery::mock(ResponseHydratorInterface::class);
        $cacheStorage = Mockery::mock(CacheStorageInterface::class);
        $cacheControl = new CacheControl(0);

        $middleware1 = new MiddlewareFixture('a', 100);
        $middleware2 = new MiddlewareFixture('b', 200);
        $middlewares = new Middlewares([
            $middleware1,
            $middleware2,
        ]);

        $guzzleConfig = [
            'handler' => new HandlerStack(Utils::chooseHandler()),
            'custom_option' => 'custom_value',
        ];

        $factory = new HttpClientFactory($responseHydrator, $guzzleConfig);
        $client = $factory->create($baseUrl, $middlewares, $cacheStorage, $cacheControl);

        Assert::type(HttpClient::class, $client);

        [
            $configuredBaseUrl,
            $configuredResponseHydrator,
            $configuredCacheStorage,
            $configuredCacheControl,
            $configuredGuzzleConfig,
            $configuredHandlerStackMiddlewares,
        ] = $this->expandHttpClientProperties($client);

        Assert::same('https://www.example.com', $configuredBaseUrl);
        Assert::same($responseHydrator, $configuredResponseHydrator);
        Assert::same($cacheStorage, $configuredCacheStorage);
        Assert::same($cacheControl, $configuredCacheControl);

        Assert::equal('custom_value', $configuredGuzzleConfig['custom_option'] ?? '');

        Assert::same([
            [$middleware2, 'b'],
            [$middleware1, 'a'],
        ], $configuredHandlerStackMiddlewares);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @return array{
     *     0: string,
     *     1: ResponseHydratorInterface,
     *     2: CacheStorageInterface,
     *     3: CacheControl,
     *     4: array<string, mixed>,
     *     5: array<int, callable>,
     * }
     */
    private function expandHttpClientProperties(HttpClient $httpClient): array
    {
        return call_user_func(Closure::bind(static function () use ($httpClient) {
            $guzzleClient = $httpClient->guzzleClient;

            [$guzzleConfig, $handlerStackMiddlewares] = call_user_func(Closure::bind(static function () use ($guzzleClient) {
                $config = $guzzleClient->config;
                $handler = $config['handler'];
                assert($handler instanceof HandlerStack);

                return [
                    $config,
                    call_user_func(Closure::bind(static fn (): array => $handler->stack, null, HandlerStack::class)),
                ];
            }, null, GuzzleClient::class));

            return [
                $httpClient->baseUrl,
                $httpClient->responseHydrator,
                $httpClient->cacheStorage,
                $httpClient->cacheControl,
                $guzzleConfig,
                $handlerStackMiddlewares,
            ];
        }, null, HttpClient::class));
    }
}

(new HttpClientFactoryTest())->run();
