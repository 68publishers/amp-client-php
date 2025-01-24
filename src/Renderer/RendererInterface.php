<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;

interface RendererInterface
{
    /**
     * @param array<string, mixed>  $elementAttributes
     * @param array<string, scalar> $options
     *
     * @throws RendererException
     */
    public function render(ResponsePosition $position, array $elementAttributes = [], array $options = []): string;

    /**
     * @param array<string, mixed>  $elementAttributes
     * @param array<string, scalar> $options
     *
     * @throws RendererException
     */
    public function renderClientSide(RequestPosition $position, array $elementAttributes = [], array $options = [], ?ClientSideMode $mode = null): string;
}
