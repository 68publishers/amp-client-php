<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\Phtml;

use Closure;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use SixtyEightPublishers\AmpClient\Tests\Renderer\AssertHtml;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class PhtmlRendererBridgeTest extends TestCase
{
    public function testTemplatesShouldBeOverridden(): void
    {
        $renderer = new PhtmlRendererBridge();
        $modifiedRenderer = $renderer->overrideTemplates(new Templates([
            Templates::Single => '/path/to/file',
        ]));

        $originalTemplates = call_user_func(Closure::bind(static fn () => $renderer->templates, null, PhtmlRendererBridge::class));
        $overriddenTemplates = call_user_func(Closure::bind(static fn () => $modifiedRenderer->templates, null, PhtmlRendererBridge::class));

        Assert::notSame($renderer, $modifiedRenderer);
        Assert::notSame($originalTemplates, $overriddenTemplates);
    }

    /**
     * @dataProvider notFoundTemplateDataProvider
     */
    public function testNotFoundTemplateRendering(
        ResponsePosition $position,
        array $elementAttributes,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderNotFound($position, $elementAttributes));
    }

    /**
     * @dataProvider singleTemplateDataProvider
     */
    public function testSingleTemplateRendering(
        ResponsePosition $position,
        ?Banner $banner,
        array $elementAttributes,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderSingle($position, $banner, $elementAttributes));
    }

    /**
     * @dataProvider randomTemplateDataProvider
     */
    public function testRandomTemplateRendering(
        ResponsePosition $position,
        ?Banner $banner,
        array $elementAttributes,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderRandom($position, $banner, $elementAttributes));
    }

    /**
     * @dataProvider multipleTemplateDataProvider
     */
    public function testMultipleTemplateRendering(
        ResponsePosition $position,
        array $banners,
        array $elementAttributes,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderMultiple($position, $banners, $elementAttributes));
    }

    /**
     * @dataProvider clientSideTemplateDataProvider
     */
    public function testClientSideTemplateRendering(
        RequestPosition $position,
        array $elementAttributes,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderClientSide($position, $elementAttributes));
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
}

(new PhtmlRendererBridgeTest())->run();
