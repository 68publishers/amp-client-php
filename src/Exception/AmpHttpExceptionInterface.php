<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface AmpHttpExceptionInterface extends AmpExceptionInterface
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ResponseInterface;
}
