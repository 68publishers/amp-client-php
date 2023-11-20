<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Exception\ResponseHydrationException;

interface ResponseHydratorInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $responseClassname
     * @param mixed           $responseBody
     *
     * @return T
     * @throws ResponseHydrationException
     */
    public function hydrate(string $responseClassname, $responseBody): object;
}
