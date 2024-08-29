<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Expression\ExpressionParserInterface;
use function array_key_exists;
use function array_merge;

final class Options
{
    /** @var array<string, scalar> */
    private array $options;

    private ExpressionParserInterface $expressionParser;

    /**
     * @param array<string, scalar> $options
     */
    public function __construct(
        array $options,
        ExpressionParserInterface $expressionParser
    ) {
        $this->options = $options;
        $this->expressionParser = $expressionParser;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function get(string $name, $defaultValue = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $defaultValue;
    }

    public function evaluate(string $name, int $index): ?string
    {
        if (!array_key_exists($name, $this->options)) {
            return null;
        }

        return $this->expressionParser->evaluateExpression(
            (string) $this->options[$name],
            $index,
        );
    }

    /**
     * @param array<string, scalar> $options
     */
    public function override(array $options): void
    {
        $this->options = array_merge(
            $this->options,
            $options,
        );
    }

    /**
     * @return array<string, scalar>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
