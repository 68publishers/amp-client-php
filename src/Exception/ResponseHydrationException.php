<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Exception;

use Exception;
use Throwable;
use function sprintf;

final class ResponseHydrationException extends Exception implements AmpExceptionInterface
{
    /**
     * @param class-string $responseClassname
     */
    public static function unableToHandleResponseClassname(string $responseClassname): self
    {
        return new self(sprintf(
            'Unable to handle response of type %s.',
            $responseClassname,
        ));
    }

    public static function malformedResponseBody(?Throwable $previous): self
    {
        return new self(
            sprintf(
                'Response body is probably malformed. %s',
                null !== $previous ? $previous->getMessage() : '',
            ),
            0,
            $previous,
        );
    }
}
