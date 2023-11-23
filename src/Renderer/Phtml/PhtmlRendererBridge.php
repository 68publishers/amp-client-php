<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Phtml;

use SixtyEightPublishers\AmpClient\Renderer\OutputBuffer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use Throwable;

final class PhtmlRendererBridge implements RendererBridgeInterface
{
    private Templates $templates;

    public function __construct()
    {
        $this->templates = new Templates([
            Templates::TemplateSingle => __DIR__ . '/Templates/single.phtml',
            Templates::TemplateRandom => __DIR__ . '/Templates/random.phtml',
            Templates::TemplateMultiple => __DIR__ . '/Templates/multiple.phtml',
            Templates::TemplateNotFound => __DIR__ . '/Templates/notFound.phtml',
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
    public function renderNotFound(Position $position): string
    {
        $filename = $this->templates->getTemplateFile(Templates::TemplateNotFound);

        return OutputBuffer::capture(function () use ($filename, $position) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderSingle(Position $position, ?Banner $banner): string
    {
        $filename = $this->templates->getTemplateFile(Templates::TemplateSingle);

        return OutputBuffer::capture(function () use ($filename, $position, $banner) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderRandom(Position $position, ?Banner $banner): string
    {
        $filename = $this->templates->getTemplateFile(Templates::TemplateRandom);

        return OutputBuffer::capture(function () use ($filename, $position, $banner) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderMultiple(Position $position, array $banners): string
    {
        $filename = $this->templates->getTemplateFile(Templates::TemplateMultiple);

        return OutputBuffer::capture(function () use ($filename, $position, $banners) {
            require $filename;
        });
    }
}
