<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient;

use InvalidArgumentException;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use function in_array;
use function rtrim;
use function sprintf;
use function strtoupper;

/**
 * @phpstan-type OptionsStructure = array{
 *      method: string,
 *      url: string,
 *      version: int,
 *      channel: string,
 *      locale: string|null,
 *      default_resources: array<int, BannerResource>,
 *      origin: string|null,
 *      cache_expiration: string|int,
 *      cache_control_header_override: string|null
 *  }
 */
final class ClientConfig
{
    public const MethodGet = 'GET';
    public const MethodPost = 'POST';

    public const Version1 = 1;

    public const Methods = [
        self::MethodGet,
        self::MethodPost,
    ];

    public const Versions = [
        self::Version1,
    ];

    private const OptMethod = 'method';
    private const OptUrl = 'url';
    private const OptVersion = 'version';
    private const OptChannel = 'channel';
    private const OptLocale = 'locale';
    private const OptDefaultResources = 'default_resources';
    private const OptOrigin = 'origin';
    private const OptCacheExpiration = 'cache_expiration';
    private const OptCacheControlHeaderOverride = 'cache_control_header_override';

    /** @var OptionsStructure */
    private array $options;

    /**
     * @param OptionsStructure $options
     */
    private function __construct(array $options)
    {
        $this->options = $options;
    }

    public static function create(string $url, string $channel): self
    {
        return new self([
            self::OptMethod => self::MethodGet,
            self::OptUrl => rtrim($url, '/'),
            self::OptVersion => self::Version1,
            self::OptChannel => $channel,
            self::OptLocale => null,
            self::OptDefaultResources => [],
            self::OptOrigin => null,
            self::OptCacheExpiration => 0,
            self::OptCacheControlHeaderOverride => null,
        ]);
    }

    public function getMethod(): string
    {
        return $this->options[self::OptMethod];
    }

    public function withMethod(string $method): self
    {
        $method = strtoupper($method);

        if (!in_array($method, self::Methods, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid method "%s" passed.',
                $method,
            ));
        }

        return $this->withOption(self::OptMethod, $method);
    }

    public function getUrl(): ?string
    {
        return $this->options[self::OptUrl];
    }

    public function withUrl(string $url): self
    {
        return $this->withOption(self::OptUrl, rtrim($url, '/'));
    }

    public function getVersion(): int
    {
        return $this->options[self::OptVersion];
    }

    public function withVersion(int $version): self
    {
        if (!in_array($version, self::Versions, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid version %d passed.',
                $version,
            ));
        }

        return $this->withOption(self::OptVersion, $version);
    }

    public function getChannel(): string
    {
        return $this->options[self::OptChannel];
    }

    public function withChannel(string $channel): self
    {
        return $this->withOption(self::OptChannel, $channel);
    }

    public function getLocale(): ?string
    {
        return $this->options[self::OptLocale];
    }

    public function withLocale(?string $locale): self
    {
        return $this->withOption(self::OptLocale, $locale);
    }

    /**
     * @return array<int, BannerResource>
     */
    public function getDefaultResources(): array
    {
        return $this->options[self::OptDefaultResources];
    }

    /**
     * @param array<int, BannerResource> $resources
     */
    public function withDefaultResources(array $resources): self
    {
        return $this->withOption(self::OptDefaultResources, $resources);
    }

    public function getOrigin(): ?string
    {
        return $this->options[self::OptOrigin];
    }

    public function withOrigin(?string $origin): self
    {
        return $this->withOption(self::OptOrigin, $origin);
    }

    /**
     * @return string|int
     */
    public function getCacheExpiration()
    {
        return $this->options[self::OptCacheExpiration];
    }

    /**
     * @param string|int $cacheExpiration
     */
    public function withCacheExpiration($cacheExpiration): self
    {
        return $this->withOption(self::OptCacheExpiration, $cacheExpiration);
    }

    public function getCacheControlHeaderOverride(): ?string
    {
        return $this->options[self::OptCacheControlHeaderOverride];
    }

    public function withCacheControlHeaderOverride(?string $cacheControlHeaderOverride): self
    {
        return $this->withOption(self::OptCacheControlHeaderOverride, $cacheControlHeaderOverride);
    }

    /**
     * @param mixed $value
     */
    private function withOption(string $key, $value): self
    {
        $options = $this->options;
        $options[$key] = $value;

        return new self($options); // @phpstan-ignore-line
    }
}
