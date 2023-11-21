<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient;

use JsonException;
use SixtyEightPublishers\AmpClient\Exception\UnexpectedErrorException;
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
use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use stdClass;
use function array_map;
use function count;
use function sprintf;

final class AmpClient implements AmpClientInterface
{
    private HttpClientFactoryInterface $httpClientFactory;

    private ClientConfig $config;

    private CacheStorageInterface $cacheStorage;

    private ?HttpClientInterface $httpClient = null;

    public function __construct(
        ClientConfig $config,
        HttpClientFactoryInterface $httpClientFactory,
        CacheStorageInterface $cacheStorage
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->cacheStorage = $cacheStorage;
    }

    public static function create(
        ClientConfig $config,
        ?HttpClientFactoryInterface $httpClientFactory = null,
        ?CacheStorageInterface $cacheStorage = null
    ): self {
        $httpClientFactory = $httpClientFactory ?? new HttpClientFactory(
            new ResponseHydrator([
                new BannersResponseHydratorHandler(),
            ]),
        );

        $cacheStorage = $cacheStorage ?? new NoCacheStorage();

        return new self(
            $config,
            $httpClientFactory,
            $cacheStorage,
        );
    }

    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    public function withConfig(ClientConfig $config): AmpClientInterface
    {
        return new self(
            $config,
            $this->httpClientFactory,
            $this->cacheStorage,
        );
    }

    public function getCacheStorage(): CacheStorageInterface
    {
        return $this->cacheStorage;
    }

    public function withCacheStorage(CacheStorageInterface $cacheStorage): AmpClientInterface
    {
        return new self(
            $this->config,
            $this->httpClientFactory,
            $cacheStorage,
        );
    }

    public function fetchBanners(BannersRequest $request): BannersResponse
    {
        $positions = $request->getPositions();

        if (0 >= count($positions)) {
            return new BannersResponse([]);
        }

        $client = $this->getHttpClient();
        $method = $this->config->getMethod();
        $defaultResources = $this->config->getDefaultResources();
        $locale = $request->getLocale() ?? $this->config->getLocale();
        $queryParam = [];

        foreach ($positions as $position) {
            $position = $position->withResources($defaultResources);
            $resources = array_map(
                static fn (BannerResource $resource): array => $resource->getValues(),
                $position->getResources(),
            );

            $queryParam[$position->getCode()] = 0 >= count($resources) ? new stdClass() : $resources;
        }

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if (ClientConfig::MethodPost === $method) {
            $body = [
                'query' => $queryParam,
            ];

            if (null !== $locale) {
                $body['locale'] = $locale;
            }

            $options['body'] = $this->jsonEncode($body);
        } else {
            $options['query'] = [
                'query' => $this->jsonEncode($queryParam),
            ];

            if (null !== $locale) {
                $options['query']['locale'] = $locale;
            }
        }

        $request = new HttpRequest(
            $method,
            'content/' . $this->config->getChannel(),
            $options,
            [
                'query' => $queryParam,
                'locale' => $locale,
            ],
        );

        return $client->request($request, BannersResponse::class);
    }

    private function getHttpClient(): HttpClientInterface
    {
        if (null !== $this->httpClient) {
            return $this->httpClient;
        }

        $baseUrl = sprintf(
            '%s/api/v%d',
            $this->config->getUrl(),
            $this->config->getVersion(),
        );

        $middlewares = new Middlewares([
            new UnexpectedErrorMiddleware(),
            new ResponseExceptionMiddleware(),
        ]);

        if (null !== $this->config->getOrigin()) {
            $middlewares = $middlewares->with(new XAmpOriginHeaderMiddleware($this->config->getOrigin()));
        }

        $cacheControl = new CacheControl(
            $this->config->getCacheExpiration(),
            $this->config->getCacheControlHeaderOverride(),
        );

        return $this->httpClient = $this->httpClientFactory->create(
            $baseUrl,
            $middlewares,
            $this->cacheStorage,
            $cacheControl,
        );
    }

    /**
     * @param mixed $value
     *
     * @throws UnexpectedErrorException
     */
    private function jsonEncode($value): string
    {
        try {
            return (string) json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedErrorException($e);
        }
    }
}
