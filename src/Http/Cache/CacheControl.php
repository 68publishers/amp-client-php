<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

final class CacheControl
{
    /** @var string|int */
    private $expiration;

    private ?CacheControlHeader $cacheControlHeaderOverride;

    /**
     * @param string|int $expiration
     */
    public function __construct(
        $expiration,
        ?string $cacheControlHeaderOverride = null
    ) {
        $this->expiration = $expiration;
        $this->cacheControlHeaderOverride = null !== $cacheControlHeaderOverride ? new CacheControlHeader([$cacheControlHeaderOverride]) : null;
    }

    public function createExpiration(): Expiration
    {
        return Expiration::create($this->expiration);
    }

    public function getCacheControlHeaderOverride(): ?CacheControlHeader
    {
        return $this->cacheControlHeaderOverride;
    }
}
