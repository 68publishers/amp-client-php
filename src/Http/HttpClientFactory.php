<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Middleware\MiddlewareInterface;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;

final class HttpClientFactory implements HttpClientFactoryInterface
{
    private ResponseHydratorInterface $responseHydrator;

    /** @var array<string, mixed> */
    private array $guzzleClientConfig;

    /** @var list<MiddlewareInterface> */
    private array $middlewares;

    /**
     * @param array<string, mixed>      $guzzleClientConfig
     * @param list<MiddlewareInterface> $middlewares
     */
    public function __construct(
        ResponseHydratorInterface $responseHydrator,
        array $guzzleClientConfig = [],
        array $middlewares = []
    ) {
        $this->responseHydrator = $responseHydrator;
        $this->guzzleClientConfig = $guzzleClientConfig;
        $this->middlewares = $middlewares;
    }

    public function create(
        string $baseUrl,
        Middlewares $middlewares,
        CacheStorageInterface $cacheStorage,
        CacheControl $cacheControl
    ): HttpClientInterface {
        $guzzleClientConfig = $this->guzzleClientConfig;
        $handlerStack = $guzzleClientConfig['handler'] ?? null;
        $handlerStack = $handlerStack instanceof HandlerStack ? clone $handlerStack : HandlerStack::create();

        $guzzleClientConfig['handler'] = $handlerStack;
        $guzzleClientConfig['http_errors'] = false;

        foreach ($this->middlewares as $middleware) {
            $middlewares = $middlewares->with($middleware);
        }

        foreach ($middlewares as $middleware) {
            $handlerStack->push($middleware, $middleware->getName()); // @phpstan-ignore-line
        }

        $guzzleClient = new Client($guzzleClientConfig);

        return new HttpClient(
            $baseUrl,
            $guzzleClient,
            $this->responseHydrator,
            $cacheStorage,
            $cacheControl,
        );
    }
}
