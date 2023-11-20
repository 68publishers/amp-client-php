<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\Hydrator;

interface ResponseHydratorHandlerInterface
{
    /**
     * @param class-string $responseClassname
     */
    public function canHydrateResponse(string $responseClassname): bool;

    /**
     * @param mixed $responseBody
     */
    public function hydrate($responseBody): object;
}
