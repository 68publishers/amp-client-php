<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\Latte;

use Closure;
use Latte\Engine;
use SixtyEightPublishers\AmpClient\Expression\ExpressionParser;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Latte\LatteRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
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
            Templates::Single => '/path/to/file',
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
        ResponsePosition $position,
        array $elementAttributes,
        array $options,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderNotFound($position, $elementAttributes, $this->createOptions($options)));
    }

    /**
     * @dataProvider singleTemplateDataProvider
     */
    public function testSingleTemplateRendering(
        ResponsePosition $position,
        ?Banner $banner,
        array $elementAttributes,
        array $options,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderSingle($position, $banner, $elementAttributes, $this->createOptions($options)));
    }

    /**
     * @dataProvider randomTemplateDataProvider
     */
    public function testRandomTemplateRendering(
        ResponsePosition $position,
        ?Banner $banner,
        array $elementAttributes,
        array $options,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderRandom($position, $banner, $elementAttributes, $this->createOptions($options)));
    }

    /**
     * @dataProvider multipleTemplateDataProvider
     * */
    public function testMultipleTemplateRendering(
        ResponsePosition $position,
        array $banners,
        array $elementAttributes,
        array $options,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderMultiple($position, $banners, $elementAttributes, $this->createOptions($options)));
    }

    /**
     * @dataProvider clientSideTemplateDataProvider
     */
    public function testClientSideTemplateRendering(
        RequestPosition $position,
        array $elementAttributes,
        array $options,
        ClientSideMode $mode,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderClientSide($position, $mode, $elementAttributes, $this->createOptions($options)));
    }

    /**
     * @dataProvider closedTemplateDataProvider
     */
    public function testClosedTemplateRendering(
        ResponsePosition $position,
        array $elementAttributes,
        array $options,
        string $expectationFile
    ): void {
        $renderer = $this->createRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderClosed($position, $elementAttributes, $this->createOptions($options)));
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

    public function clientSideTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/client-side/data-provider.php';
    }

    public function closedTemplateDataProvider(): array
    {
        return require __DIR__ . '/../../resources/renderer/closed/data-provider.php';
    }

    private function createRendererBridge(): LatteRendererBridge
    {
        return LatteRendererBridge::fromEngine(new Engine());
    }

    private function createOptions(array $options): Options
    {
        return new Options($options, new ExpressionParser());
    }
}

(new LatteRendererBridgeTest())->run();
