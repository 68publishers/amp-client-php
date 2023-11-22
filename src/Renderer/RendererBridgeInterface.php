<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

interface RendererBridgeInterface
{
    public function renderNotFound(Position $position): string;

    public function renderSingle(Position $position, ?Banner $banner): string;

    public function renderRandom(Position $position, ?Banner $banner): string;

    /**
     * @param array<int, Banner> $banners
     */
    public function renderMultiple(Position $position, array $banners): string;
}
