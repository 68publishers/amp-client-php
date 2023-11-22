<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

interface RendererInterface
{
    /**
     * @throws RendererException
     */
    public function render(Position $position): string;
}
