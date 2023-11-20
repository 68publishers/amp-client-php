<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;
use SixtyEightPublishers\AmpClient\Exception\UnexpectedErrorException;
use Throwable;

final class UnexpectedErrorMiddleware implements MiddlewareInterface
{
    public function getName(): string
    {
        return 'unexpected_error';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function __invoke(Closure $next): Closure
    {
        return static function (RequestInterface $request, array $options) use ($next): PromiseInterface {
            try {
                return $next($request, $options);
            } catch (Throwable $e) {
                throw ($e instanceof AmpExceptionInterface ? $e : new UnexpectedErrorException($e));
            }
        };
    }
}
