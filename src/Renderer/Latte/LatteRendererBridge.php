<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use Latte\Engine;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\MultipleTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\NotFoundTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\RandomTemplate;
use SixtyEightPublishers\AmpClient\Renderer\Latte\Templates\SingleTemplate;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class LatteRendererBridge implements RendererBridgeInterface
{
    private LatteFactoryInterface $latteFactory;

    private Templates $templates;

    private ?Engine $latte = null;

    public function __construct(LatteFactoryInterface $latteFactory)
    {
        $this->latteFactory = $latteFactory;
        $this->templates = new Templates([
            Templates::TemplateSingle => __DIR__ . '/Templates/single.latte',
            Templates::TemplateRandom => __DIR__ . '/Templates/random.latte',
            Templates::TemplateMultiple => __DIR__ . '/Templates/multiple.latte',
            Templates::TemplateNotFound => __DIR__ . '/Templates/notFound.latte',
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

    public function renderNotFound(Position $position): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::TemplateNotFound),
            new NotFoundTemplate($position),
        );
    }

    public function renderSingle(Position $position, ?Banner $banner): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::TemplateSingle),
            new SingleTemplate($position, $banner),
        );
    }

    public function renderRandom(Position $position, ?Banner $banner): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::TemplateRandom),
            new RandomTemplate($position, $banner),
        );
    }

    public function renderMultiple(Position $position, array $banners): string
    {
        return $this->getLatte()->renderToString(
            $this->templates->getTemplateFile(Templates::TemplateMultiple),
            new MultipleTemplate($position, $banners),
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
