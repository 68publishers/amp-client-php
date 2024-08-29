<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Expression;

interface ExpressionParserInterface
{
    public function parseExpression(string $expression): ParsedExpression;

    public function evaluateExpression(string $expression, int $index): ?string;
}
