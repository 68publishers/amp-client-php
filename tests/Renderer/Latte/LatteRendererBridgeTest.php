<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\Latte;

use Closure;
use Latte\Engine;
use SixtyEightPublishers\AmpClient\Renderer\Latte\LatteRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Tests\Renderer\AssertHtml;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class LatteRendererBridgeTest extends TestCase
{
    public function testTemplatesShouldBeOverridden(): void
    {
        $renderer = $this->createRendererBridge();
        $modifiedRenderer = $renderer->overrideTemplates(new Templates([
            Templates::TemplateSingle => '/path/to/file',
        ]));

        $originalTemplates = call_user_func(Closure::bind(static fn () => $renderer->templates, null, LatteRendererBridge::class));
        $overriddenTemplates = call_user_func(Closure::bind(static fn () => $modifiedRenderer->templates, null, LatteRendererBridge::class));

        Assert::notSame($renderer, $modifiedRenderer);
        Assert::notSame($originalTemplates, $overriddenTemplates);
    }

    /**
     * @dataProvider notFoundTemplateDataProvider
     */
    public function testNotFoundTemplateRendering(
        Position $position,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderNotFound($position));
    }

    /**
     * @dataProvider singleTemplateDataProvider
     */
    public function testSingleTemplateRendering(
        Position $position,
        ?Banner $banner,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderSingle($position, $banner));
    }

    /**
     * @dataProvider randomTemplateDataProvider
     */
    public function testRandomTemplateRendering(
        Position $position,
        ?Banner $banner,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderRandom($position, $banner));
    }

    /**
     * @dataProvider multipleTemplateDataProvider
     * */
    public function testMultipleTemplateRendering(
        Position $position,
        array $banners,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderMultiple($position, $banners));
    }

    public function notFoundTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/not-found/data-provider.php';
    }

    public function singleTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/single/data-provider.php';
    }

    public function randomTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/random/data-provider.php';
    }

    public function multipleTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/multiple/data-provider.php';
    }

    private function createRendererBridge(): LatteRendererBridge
    {
        return LatteRendererBridge::fromEngine(new Engine());
    }
}

(new LatteRendererBridgeTest())->run();
