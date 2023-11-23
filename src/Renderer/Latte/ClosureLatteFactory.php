<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use Closure;
use Latte\Engine;
use function assert;

final class ClosureLatteFactory implements LatteFactoryInterface
{
    /** @var Closure(): Engine */
    private Closure $factory;

    /**
     * @param Closure(): Engine $factory
     */
    public function __construct(Closure $factory)
    {
        $this->factory = $factory;
    }

    public function create(): Engine
    {
        $latte = ($this->factory)();

        assert($latte instanceof Engine);

        return $latte;
    }
}
