<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

use Psr\Http\Message\ResponseInterface;
use function array_key_exists;
use function count;
use function preg_match_all;

final class CacheControlHeader
{
    const Regex = '/(?:^|(?:\s*\,\s*))([^\x00-\x20\(\)<>@\,;\:\\\\"\/\[\]\?\=\{\}\x7F]+)(?:\=(?:([^\x00-\x20\(\)<>@\,;\:\\\\"\/\[\]\?\=\{\}\x7F]+)|(?:\"((?:[^"\\\\]|\\\\.)*)\")))?/';

    /** @var array<string, string> */
    private array $values = [];

    /**
     * @param array<int, string> $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $value) {
            $matches = [];
            if (preg_match_all(self::Regex, $value, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $val = '';
                    if (count($match) == 3) {
                        $val = $match[2];
                    } elseif (count($match) > 3) {
                        $val = $match[3];
                    }

                    $this->values[$match[1]] = $val;
                }
            }
        }
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        return new self($response->getHeader('cache-control'));
    }

    public function has(string $key): bool
    {
        return isset($this->values[$key]) || array_key_exists($key, $this->values);
    }

    public function get(string $key, string $default = ''): string
    {
        if ($this->has($key)) {
            return $this->values[$key];
        }

        return $default;
    }

    public function isEmpty(): bool
    {
        return 0 >= count($this->values);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }
}
