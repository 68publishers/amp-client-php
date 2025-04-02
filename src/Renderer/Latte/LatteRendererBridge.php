<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use Latte\Engine;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\ClientSideTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\ClosedTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\MultipleTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\NotFoundTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\RandomTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\SingleTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;

final class LatteRendererBridge implements RendererBridgeInterface
{
    private LatteFactoryInterface $latteFactory;

    private Templates $templates;

    private ?Engine $latte = null;

    public function __construct(LatteFactoryInterface $latteFactory)
    {
        $this->latteFactory = $latteFactory;
        $this->templates = new Templates([
            Templates::Single => __DIR__ . '/Templates/single.latte',
            Templates::Random => __DIR__ . '/Templates/random.latte',
            Templates::Multiple => __DIR__ . '/Templates/multiple.latte',
            Templates::NotFound => __DIR__ . '/Templates/notFound.latte',
            Templates::ClientSide => __DIR__ . '/Templates/clientSide.latte',
            Templates::Closed => __DIR__ . '/Templates/closed.latte',
        ]);
    }

    public static function fromEngine(Engine $engine): self
    {
        return new self(
            new ClosureLatteFactory(static fn (): Engine => $engine),
        );
    }

    public function overrideTemplates(Templates $templates): self
    {
        $renderer = clone $this;
        $renderer->templates = $this->templates->override($templates);

        return $renderer;
    }

    public function renderNotFound(ResponsePosition $position, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::NotFound),
            new NotFoundTemplate($position, $elementAttributes, $options),
        );
    }

    public function renderSingle(ResponsePosition $position, ?Banner $banner, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::Single),
            new SingleTemplate($position, $banner, $elementAttributes, $options),
        );
    }

    public function renderRandom(ResponsePosition $position, ?Banner $banner, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::Random),
            new RandomTemplate($position, $banner, $elementAttributes, $options),
        );
    }

    public function renderMultiple(ResponsePosition $position, array $banners, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::Multiple),
            new MultipleTemplate($position, $banners, $elementAttributes, $options),
        );
    }

    public function renderClientSide(RequestPosition $position, ClientSideMode $mode, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::ClientSide),
            new ClientSideTemplate($position, $mode, $elementAttributes, $options),
        );
    }

    public function renderClosed(ResponsePosition $position, array $elementAttributes, Options $options): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::Closed),
            new ClosedTemplate($position, $elementAttributes, $options),
        );
    }

    private function getLatte(): Engine
    {
        if (null === $this->latte) {
            $this->latte = $this->latteFactory->create();
        }

        return $this->latte;
    }
}
