<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

interface RendererBridgeInterface
{
    public function overrideTemplates(Templates $templates): self;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderNotFound(Position $position, array $elementAttributes = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderSingle(Position $position, ?Banner $banner, array $elementAttributes = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderRandom(Position $position, ?Banner $banner, array $elementAttributes = []): string;

    /**
     * @param array<int, Banner>         $banners
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderMultiple(Position $position, array $banners, array $elementAttributes = []): string;
}
