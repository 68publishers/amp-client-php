<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Exception;

use Exception;
use Throwable;

final class UnexpectedErrorException extends Exception implements AmpExceptionInterface
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('Client thrown an unexpected exception: ' . $previous->getMessage(), $previous->getCode(), $previous);
    }
}
