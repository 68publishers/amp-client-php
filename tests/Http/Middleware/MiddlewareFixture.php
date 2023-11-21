<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\FulfilledPromise;
use SixtyEightPublishers\AmpClient\Http\Middleware\MiddlewareInterface;

final class MiddlewareFixture implements MiddlewareInterface
{
    private string $name;

    private int $priority;

    /** @var mixed */
    private $result;

    /**
     * @param mixed $result
     */
    public function __construct(
        string $name,
        int $priority,
        $result = null
    ) {
        $this->name = $name;
        $this->priority = $priority;
        $this->result = $result;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function __invoke(Closure $next): Closure
    {
        return static fn (): FulfilledPromise => new FulfilledPromise($this->result);
    }
}
