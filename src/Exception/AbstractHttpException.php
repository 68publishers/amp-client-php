<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Exception;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class AbstractHttpException extends Exception implements AmpHttpExceptionInterface
{
    private RequestInterface $request;

    private ResponseInterface $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        string $message,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $response->getStatusCode(), $previous);

        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
