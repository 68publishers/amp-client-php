<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http;

use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;

interface HttpClientInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $responseClassname
     *
     * @return T
     * @throws AmpExceptionInterface
     */
    public function request(HttpRequest $request, string $responseClassname): object;
}
