<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

final class CachedResponse
{
    private CacheKey $key;

    private object $response;

    private Expiration $maxAge;

    private ?Etag $etag;

    public function __construct(
        CacheKey $key,
        object $response,
        Expiration $maxAge,
        ?Etag $etag
    ) {
        $this->key = $key;
        $this->response = $response;
        $this->maxAge = $maxAge;
        $this->etag = $etag;
    }

    public function getKey(): CacheKey
    {
        return $this->key;
    }

    public function getResponse(): object
    {
        return $this->response;
    }

    public function getMaxAge(): Expiration
    {
        return $this->maxAge;
    }

    public function isFresh(): bool
    {
        return $this->getMaxAge()->isFresh();
    }

    public function getEtag(): ?Etag
    {
        return $this->etag;
    }
}
