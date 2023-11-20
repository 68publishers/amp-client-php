<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

use JsonException;
use SixtyEightPublishers\AmpClient\Exception\UnexpectedErrorException;
use function base64_encode;
use function dechex;
use function hash;
use function json_encode;
use function mb_strlen;
use function mb_substr;

final class CacheKey
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param array<string, mixed> $components
     *
     * @throws UnexpectedErrorException
     */
    public static function compute(array $components): self
    {
        try {
            $payload = json_encode($components, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedErrorException($e);
        }

        $hash = mb_substr(
            base64_encode(
                hash('sha1', $payload, true),
            ),
            0,
            27,
        );

        return new self(
            dechex(mb_strlen($payload, 'UTF-8')) . '-' . $hash,
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
