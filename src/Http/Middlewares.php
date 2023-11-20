<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use ArrayIterator;
use IteratorAggregate;
use SixtyEightPublishers\AmpClient\Http\Middleware\MiddlewareInterface;
use function usort;

/**
 * @implements IteratorAggregate<int, MiddlewareInterface>
 */
final class Middlewares implements IteratorAggregate
{
    /** @var array<int, MiddlewareInterface> */
    private array $middlewares;

    /**
     * @param array<int, MiddlewareInterface> $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function with(MiddlewareInterface $middleware): self
    {
        $middlewares = $this->middlewares;
        $middlewares[] = $middleware;

        return new self($middlewares);
    }

    /**
     * @return ArrayIterator<int, MiddlewareInterface>
     */
    public function getIterator(): ArrayIterator
    {
        $middlewares = $this->middlewares;

        usort(
            $middlewares,
            static fn (MiddlewareInterface $left, MiddlewareInterface $right): int => $right->getPriority() <=> $left->getPriority(),
        );

        return new ArrayIterator($middlewares);
    }
}
