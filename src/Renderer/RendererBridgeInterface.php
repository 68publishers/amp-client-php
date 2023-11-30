<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;

interface RendererBridgeInterface
{
    public function overrideTemplates(Templates $templates): self;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderNotFound(ResponsePosition $position, array $elementAttributes = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderSingle(ResponsePosition $position, ?Banner $banner, array $elementAttributes = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderRandom(ResponsePosition $position, ?Banner $banner, array $elementAttributes = []): string;

    /**
     * @param array<int, Banner>         $banners
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderMultiple(ResponsePosition $position, array $banners, array $elementAttributes = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function renderClientSide(RequestPosition $position, array $elementAttributes = []): string;
}
