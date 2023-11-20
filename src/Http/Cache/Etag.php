<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

use Psr\Http\Message\ResponseInterface;

final class Etag
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromResponse(ResponseInterface $response): ?self
    {
        $headerValue = $response->getHeader('ETag')[0] ?? null;

        return null !== $headerValue && '' !== $headerValue ? new self($headerValue) : null;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
