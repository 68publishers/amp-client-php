<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Expression;

use function array_map;
use function explode;
use function preg_match;
use function trim;

final class ExpressionParser implements ExpressionParserInterface
{
    private const ExpressionRegex = '/^(?:(?<INTERVAL_FROM>\d+)-(?<INTERVAL_TO>\d+):|(?<EQ>\d+):|<(?<LT>\d+):|<=(?<LTE>\d+):|>(?<GT>\d+):|>=(?<GTE>\d+):)?(?<VALUE>[^:\s]+)$/';

    /** @var array<string, ParsedExpression> */
    private array $cache = [];

    public function parseExpression(string $expression): ParsedExpression
    {
        if (isset($this->cache[$expression])) {
            return $this->cache[$expression];
        }

        $parts = array_map(
            static fn (string $part) => trim($part),
            explode(',', $expression),
        );
        $rules = [];

        foreach ($parts as $part) {
            if (!preg_match(self::ExpressionRegex, $part, $matches)) {
                continue;
            }

            $value = $matches['VALUE'];

            switch (true) {
                case '' !== $matches['INTERVAL_FROM'] && '' !== $matches['INTERVAL_TO']:
                    $rules[] = ExpressionRule::range(
                        (int) $matches['INTERVAL_FROM'],
                        (int) $matches['INTERVAL_TO'],
                        $value,
                    );

                    break;
                case '' !== $matches['EQ']:
                    $rules[] = ExpressionRule::eq(
                        (int) $matches['EQ'],
                        $value,
                    );

                    break;
                case '' !== $matches['LT']:
                    $rules[] = ExpressionRule::lt(
                        (int) $matches['LT'],
                        $value,
                    );

                    break;
                case '' !== $matches['LTE']:
                    $rules[] = ExpressionRule::lte(
                        (int) $matches['LTE'],
                        $value,
                    );

                    break;
                case '' !== $matches['GT']:
                    $rules[] = ExpressionRule::gt(
                        (int) $matches['GT'],
                        $value,
                    );

                    break;
                case '' !== $matches['GTE']:
                    $rules[] = ExpressionRule::gte(
                        (int) $matches['GTE'],
                        $value,
                    );

                    break;
                default:
                    $rules[] = ExpressionRule::positive($value);
            }
        }

        return $this->cache[$expression] = new ParsedExpression($rules);
    }

    public function evaluateExpression(string $expression, int $index): ?string
    {
        return $this->parseExpression($expression)->evaluate($index);
    }
}
