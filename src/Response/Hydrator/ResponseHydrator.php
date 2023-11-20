<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Exception\ResponseHydrationException;
use function assert;

final class ResponseHydrator implements ResponseHydratorInterface
{
    /** @var array<int, ResponseHydratorHandlerInterface> */
    private array $handlers;

    /**
     * @param array<int, ResponseHydratorHandlerInterface> $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    public function hydrate(string $responseClassname, $responseBody): object
    {
        foreach ($this->handlers as $hydrator) {
            if ($hydrator->canHydrateResponse($responseClassname)) {
                $response = $hydrator->hydrate($responseBody);

                assert($response instanceof $responseClassname);

                return $response;
            }
        }

        throw ResponseHydrationException::unableToHandleResponseClassname($responseClassname);
    }
}
