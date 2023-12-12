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
     * @param array<string, scalar>      $options
     */
    public function renderNotFound(ResponsePosition $position, array $elementAttributes = [], array $options = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function renderSingle(ResponsePosition $position, ?Banner $banner, array $elementAttributes = [], array $options = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function renderRandom(ResponsePosition $position, ?Banner $banner, array $elementAttributes = [], array $options = []): string;

    /**
     * @param array<int, Banner>         $banners
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function renderMultiple(ResponsePosition $position, array $banners, array $elementAttributes = [], array $options = []): string;

    /**
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function renderClientSide(RequestPosition $position, ClientSideMode $mode, array $elementAttributes = [], array $options = []): string;
}
