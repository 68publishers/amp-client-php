<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Expression;

use SixtyEightPublishers\AmpClient\Expression\ExpressionParser;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ExpressionParserTest extends TestCase
{
    private ExpressionParser $parser;

    public function testEvaluateWithoutCondition(): void
    {
        $expression = 'foo';

        Assert::same('foo', $this->parser->evaluateExpression($expression, 0));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 1));
    }

    public function testEvaluateEq(): void
    {
        $expression = '1:foo';

        Assert::null($this->parser->evaluateExpression($expression, 0));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 1));
        Assert::null($this->parser->evaluateExpression($expression, 2));
    }

    public function testEvaluateRange(): void
    {
        $expression = '1-2:foo';

        Assert::null($this->parser->evaluateExpression($expression, 0));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 1));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 2));
        Assert::null($this->parser->evaluateExpression($expression, 3));
    }

    public function testEvaluateLt(): void
    {
        $expression = '>1:foo';

        Assert::null($this->parser->evaluateExpression($expression, 0));
        Assert::null($this->parser->evaluateExpression($expression, 1));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 2));
    }

    public function testEvaluateLte(): void
    {
        $expression = '>=1:foo';

        Assert::null($this->parser->evaluateExpression($expression, 0));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 1));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 2));
    }

    public function testEvaluateGt(): void
    {
        $expression = '<1:foo';

        Assert::same('foo', $this->parser->evaluateExpression($expression, 0));
        Assert::null($this->parser->evaluateExpression($expression, 1));
    }

    public function testEvaluateGte(): void
    {
        $expression = '<=1:foo';

        Assert::same('foo', $this->parser->evaluateExpression($expression, 0));
        Assert::same('foo', $this->parser->evaluateExpression($expression, 1));
        Assert::null($this->parser->evaluateExpression($expression, 2));
    }

    public function evaluateMultipleRules(): void
    {
        $expression = '0:large,1-3:medium,<5:normal,thin';

        Assert::same('large', $this->parser->evaluateExpression($expression, 0));
        Assert::same('medium', $this->parser->evaluateExpression($expression, 1));
        Assert::same('medium', $this->parser->evaluateExpression($expression, 2));
        Assert::same('medium', $this->parser->evaluateExpression($expression, 3));
        Assert::same('normal', $this->parser->evaluateExpression($expression, 4));
        Assert::same('thin', $this->parser->evaluateExpression($expression, 5));
        Assert::same('thin', $this->parser->evaluateExpression($expression, 6));
    }

    protected function setUp(): void
    {
        $this->parser = new ExpressionParser();
    }
}

(new ExpressionParserTest())->run();
