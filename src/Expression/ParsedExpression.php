<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Expression;

use function array_key_exists;

final class ParsedExpression
{
    /** @var list<ExpressionRule> */
    private array $rules;

    /** @var array<string, string|null> */
    private array $cache = [];

    /**
     * @param list<ExpressionRule> $rules
     */
    public function __construct(
        array $rules
    ) {
        $this->rules = $rules;
    }

    public function evaluate(int $index): ?string
    {
        $cacheKey = 'i_' . $index;

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        foreach ($this->rules as $rule) {
            if ($rule->matches($index)) {
                return $this->cache[$cacheKey] = $rule->value;
            }
        }

        return $this->cache[$cacheKey] = null;
    }
}
