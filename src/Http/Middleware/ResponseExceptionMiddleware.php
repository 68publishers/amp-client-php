<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SixtyEightPublishers\AmpClient\Exception\BadRequestException;
use SixtyEightPublishers\AmpClient\Exception\NotFoundException;
use SixtyEightPublishers\AmpClient\Exception\ServerErrorException;
use function json_decode;
use function strpos;

final class ResponseExceptionMiddleware implements MiddlewareInterface
{
    public function getName(): string
    {
        return 'response_exception';
    }

    public function getPriority(): int
    {
        return 90;
    }

    public function __invoke(Closure $next): Closure
    {
        return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
            return $next($request, $options)->then(
                function (ResponseInterface $response) use ($request) {
                    $statusCode = $response->getStatusCode();

                    if (400 > $statusCode) {
                        return $response;
                    }

                    $errorMessage = false !== strpos($response->getHeaderLine('content-type'), 'application/json')
                        ? $this->getErrorMessage($response)
                        : (string) $response->getBody();

                    if (404 === $statusCode) {
                        throw new NotFoundException($request, $response, $errorMessage);
                    }

                    if (500 > $statusCode) {
                        throw new BadRequestException($request, $response, $errorMessage);
                    }

                    throw new ServerErrorException($request, $response, $errorMessage);
                },
            );
        };
    }

    private function getErrorMessage(ResponseInterface $response): string
    {
        $responseBodyString = (string) $response->getBody();

        try {
            $responseBodyJson = json_decode($responseBodyString, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $responseBodyString;
        }

        if (500 <= $response->getStatusCode()) {
            return (string) ($responseBodyJson->message ?? $responseBodyString);
        }

        return (string) ($responseBodyJson->data->error ?? $responseBodyString);
    }
}
