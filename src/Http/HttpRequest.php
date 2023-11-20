<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use function strtoupper;

final class HttpRequest
{
    private string $method;

    private string $url;

    /** @var array<string, mixed> */
    private array $options;

    /** @var array<string, mixed>|null */
    private ?array $cacheComponents;

    /**
     * @param array<string, mixed>      $options
     * @param array<string, mixed>|null $cacheComponents
     */
    public function __construct(string $method, string $url, array $options = [], ?array $cacheComponents = null)
    {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->options = $options;
        $this->cacheComponents = $cacheComponents;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): self
    {
        return new self(
            $this->method,
            $this->url,
            $options,
            $this->cacheComponents,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCacheComponents(): ?array
    {
        return $this->cacheComponents;
    }
}
