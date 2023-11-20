<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface MiddlewareInterface
{
    public function getName(): string;

    public function getPriority(): int;

    /**
     * @param Closure(RequestInterface $request, array<string, mixed> $options): PromiseInterface $next
     *
     * @return Closure(RequestInterface $request, array<string, mixed> $options): PromiseInterface
     */
    public function __invoke(Closure $next): Closure;
}
