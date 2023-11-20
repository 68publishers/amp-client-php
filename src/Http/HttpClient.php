<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SixtyEightPublishers\AmpClient\Exception\ResponseHydrationException;
use SixtyEightPublishers\AmpClient\Exception\UnexpectedErrorException;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControl;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControlHeader;
use SixtyEightPublishers\AmpClient\Http\Cache\CachedResponse;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheKey;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\Etag;
use SixtyEightPublishers\AmpClient\Http\Cache\Expiration;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;
use Throwable;
use function array_merge;
use function assert;
use function json_decode;
use function ltrim;
use function rtrim;

final class HttpClient implements HttpClientInterface
{
    private string $baseUrl;

    private GuzzleClientInterface $guzzleClient;

    private ResponseHydratorInterface $responseHydrator;

    private CacheStorageInterface $cacheStorage;

    private CacheControl $cacheControl;

    public function __construct(
        string $baseUrl,
        GuzzleClientInterface $guzzleClient,
        ResponseHydratorInterface $responseHydrator,
        CacheStorageInterface $cacheStorage,
        CacheControl $cacheControl
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->guzzleClient = $guzzleClient;
        $this->responseHydrator = $responseHydrator;
        $this->cacheStorage = $cacheStorage;
        $this->cacheControl = $cacheControl;
    }

    /**
     * @throws GuzzleException
     * @throws UnexpectedErrorException
     * @throws ResponseHydrationException
     */
    public function request(HttpRequest $request, string $responseClassname): object
    {
        $url = $this->baseUrl . '/' . ltrim($request->getUrl());
        $cacheComponents = $request->getCacheComponents();

        if (null !== $cacheComponents) {
            $cacheComponents['__url'] = $url;
            $cacheComponents['__method'] = $request->getMethod();
        }

        $cacheKey = null !== $cacheComponents ? CacheKey::compute($cacheComponents) : null;
        $cachedResponse = null !== $cacheKey ? $this->cacheStorage->get($cacheKey) : null;

        if (null !== $cachedResponse) {
            if ($cachedResponse->getMaxAge()->isFresh()) {
                assert($cachedResponse->getResponse() instanceof $responseClassname);

                return $cachedResponse->getResponse();
            }

            if (null !== $cachedResponse->getEtag()) {
                $currentOptions = $request->getOptions();
                $request = $request->withOptions(array_merge(
                    $currentOptions,
                    [
                        'headers' => array_merge(
                            $currentOptions['headers'] ?? [],
                            [
                                'if-none-match' => $cachedResponse->getEtag()->getValue(),
                            ],
                        ),
                    ],
                ));
            }
        }

        $response = $this->guzzleClient->request($request->getMethod(), $url, $request->getOptions());
        $cacheControlHeader = $this->cacheControl->getCacheControlHeaderOverride() ?? CacheControlHeader::fromResponse($response);
        $etag = Etag::fromResponse($response);

        $canBeStored = !$cacheControlHeader->has('no-store');
        $maxAge = 0;

        if ($canBeStored && !$cacheControlHeader->has('no-cache')) {
            if ('' !== $cacheControlHeader->get('s-maxage')) {
                $maxAge = (int) $cacheControlHeader->get('s-maxage');
            } elseif ('' !== $cacheControlHeader->get('max-age')) {
                $maxAge = (int) $cacheControlHeader->get('max-age');
            }
        }

        $mappedResponse = 304 === $response->getStatusCode() && null !== $cachedResponse
            ? $cachedResponse->getResponse()
            : $this->responseHydrator->hydrate($responseClassname, $this->getJsonFromResponseBody($response));

        assert($mappedResponse instanceof $responseClassname);

        if (null !== $cacheKey && $canBeStored) {
            $cachedResponse = new CachedResponse(
                $cacheKey,
                $mappedResponse,
                Expiration::create($maxAge),
                $etag,
            );

            $this->cacheStorage->save($cachedResponse, $this->cacheControl->createExpiration());
        }

        return $mappedResponse;
    }

    /**
     * @return mixed
     * @throws ResponseHydrationException
     */
    protected function getJsonFromResponseBody(ResponseInterface $response)
    {
        try {
            return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw ResponseHydrationException::malformedResponseBody($e);
        }
    }
}
