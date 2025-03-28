<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Phtml;

use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Renderer\OutputBuffer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use Throwable;

final class PhtmlRendererBridge implements RendererBridgeInterface
{
    private Templates $templates;

    public function __construct()
    {
        $this->templates = new Templates([
            Templates::Single => __DIR__ . '/Templates/single.phtml',
            Templates::Random => __DIR__ . '/Templates/random.phtml',
            Templates::Multiple => __DIR__ . '/Templates/multiple.phtml',
            Templates::NotFound => __DIR__ . '/Templates/notFound.phtml',
            Templates::ClientSide => __DIR__ . '/Templates/clientSide.phtml',
            Templates::Closed => __DIR__ . '/Templates/closed.phtml',
        ]);
    }

    public function overrideTemplates(Templates $templates): self
    {
        $renderer = clone $this;
        $renderer->templates = $this->templates->override($templates);

        return $renderer;
    }

    /**
     * @throws Throwable
     */
    public function renderNotFound(ResponsePosition $position, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::NotFound);

        return OutputBuffer::capture(function () use ($filename, $position, $elementAttributes, $options) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderSingle(ResponsePosition $position, ?Banner $banner, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::Single);

        return OutputBuffer::capture(function () use ($filename, $position, $banner, $elementAttributes, $options) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderRandom(ResponsePosition $position, ?Banner $banner, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::Random);

        return OutputBuffer::capture(function () use ($filename, $position, $banner, $elementAttributes, $options) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderMultiple(ResponsePosition $position, array $banners, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::Multiple);

        return OutputBuffer::capture(function () use ($filename, $position, $banners, $elementAttributes, $options) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderClientSide(RequestPosition $position, ClientSideMode $mode, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::ClientSide);

        return OutputBuffer::capture(function () use ($filename, $position, $elementAttributes, $options, $mode) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderClosed(ResponsePosition $position, array $elementAttributes, Options $options): string
    {
        $filename = $this->templates->getTemplateFile(Templates::Closed);

        return OutputBuffer::capture(function () use ($filename, $position, $elementAttributes, $options) {
            require $filename;
        });
    }
}
