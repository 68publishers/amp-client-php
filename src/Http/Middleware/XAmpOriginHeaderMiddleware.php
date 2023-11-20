<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

final class XAmpOriginHeaderMiddleware implements MiddlewareInterface
{
    private string $origin;

    public function __construct(string $origin)
    {
        $this->origin = $origin;
    }

    public function getName(): string
    {
        return 'x_amp_origin_header';
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function __invoke(Closure $next): Closure
    {
        return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
            $request = $request->withHeader('X-Amp-Origin', $this->origin);

            return $next($request, $options);
        };
    }
}
