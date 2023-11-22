<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\Phtml;

use Closure;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Tests\Renderer\AssertHtml;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class PhtmlRendererBridgeTest extends TestCase
{
    public function testTemplatesShouldBeOverwritten(): void
    {
        $renderer = new PhtmlRendererBridge([
            PhtmlRendererBridge::TemplateSingle => '/path/to/template.phtml',
        ]);

        $templates = call_user_func(Closure::bind(static fn () => $renderer->templates, null, PhtmlRendererBridge::class));

        Assert::same('/path/to/template.phtml', $templates[PhtmlRendererBridge::TemplateSingle]);
    }

    public function testRendererExceptionShouldBeThrownWhenTemplateFileNotExists(): void
    {
        $renderer = new PhtmlRendererBridge([
            PhtmlRendererBridge::TemplateSingle => 'non-existent.single.phtml',
            PhtmlRendererBridge::TemplateMultiple => 'non-existent.multiple.phtml',
            PhtmlRendererBridge::TemplateRandom => 'non-existent.random.phtml',
            PhtmlRendererBridge::TemplateNotFound => 'non-existent.not-found.phtml',
        ]);

        Assert::exception(
            static function () use ($renderer): void {
                $banner = new Banner('1234', 'Main', 0, null, null, null, []);
                $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeSingle, Position::BreakpointTypeMin, [$banner]);

                $renderer->renderSingle($position, $banner);
            },
            RendererException::class,
            'Template file "non-existent.single.phtml" not found.',
        );

        Assert::exception(
            static function () use ($renderer): void {
                $banner = new Banner('1234', 'Main', 0, null, null, null, []);
                $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeMultiple, Position::BreakpointTypeMin, [$banner]);

                $renderer->renderMultiple($position, [$banner]);
            },
            RendererException::class,
            'Template file "non-existent.multiple.phtml" not found.',
        );

        Assert::exception(
            static function () use ($renderer): void {
                $banner = new Banner('1234', 'Main', 0, null, null, null, []);
                $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeRandom, Position::BreakpointTypeMin, [$banner]);

                $renderer->renderRandom($position, $banner);
            },
            RendererException::class,
            'Template file "non-existent.random.phtml" not found.',
        );

        Assert::exception(
            static function () use ($renderer): void {
                $position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []);

                $renderer->renderNotFound($position);
            },
            RendererException::class,
            'Template file "non-existent.not-found.phtml" not found.',
        );
    }

    /**
     * @dataProvider notFoundTemplateDataProvider
     */
    public function testNotFoundTemplateRendering(
        Position $position,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

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
        $renderer = new PhtmlRendererBridge();

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
        $renderer = new PhtmlRendererBridge();

        AssertHtml::assert($expectationFile, $renderer->renderRandom($position, $banner));
    }

    /**
     * @dataProvider multipleTemplateDataProvider
     */
    public function testMultipleTemplateRendering(
        Position $position,
        array $banners,
        string $expectationFile
    ): void {
        $renderer = new PhtmlRendererBridge();

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
}

(new PhtmlRendererBridgeTest())->run();
