<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorHandlerInterface;
use stdClass;

final class ResponseHydratorHandlerFixture implements ResponseHydratorHandlerInterface
{
    private string $responseClassname;

    private object $result;

    public function __construct(
        string $responseClassname,
        ?object $result = null
    ) {
        $this->responseClassname = $responseClassname;
        $this->result = $result ?? new stdClass();
    }

    public function canHydrateResponse(string $responseClassname): bool
    {
        return $this->responseClassname === $responseClassname;
    }

    public function hydrate($responseBody): object
    {
        return $this->result;
    }
}
