<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use Closure;
use Exception;
use Mockery;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolverInterface;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../bootstrap.php';

final class RendererTest extends TestCase
{
    public function testDefaultRendererShouldBeCreated(): void
    {
        $renderer = Renderer::create();

        [$bannersResolver, $rendererBridge] = call_user_func(Closure::bind(static function () use ($renderer): array {
            return [
                $renderer->bannersResolver,
                $renderer->rendererBridge,
            ];
        }, null, Renderer::class));

        Assert::equal(new BannersResolver(), $bannersResolver);
        Assert::equal(new PhtmlRendererBridge(), $rendererBridge);
    }

    public function testNotFoundTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []);

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [])
            ->andReturn('not found');

        Assert::same('not found', $renderer->render($position));
    }

    public function testSingleTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $banner = new Banner('1234', 'Main', 0, null, null, null, []);
        $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeSingle, Position::BreakpointTypeMin, [$banner]);

        $bannersResolver
            ->shouldReceive('resolveSingle')
            ->once()
            ->with($position)
            ->andReturn($banner);

        $rendererBridge
            ->shouldReceive('renderSingle')
            ->once()
            ->with($position, $banner, [])
            ->andReturn('single');

        Assert::same('single', $renderer->render($position));
    }

    public function testRandomTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $banner = new Banner('1234', 'Main', 0, null, null, null, []);
        $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeRandom, Position::BreakpointTypeMin, [$banner]);

        $bannersResolver
            ->shouldReceive('resolveRandom')
            ->once()
            ->with($position)
            ->andReturn($banner);

        $rendererBridge
            ->shouldReceive('renderRandom')
            ->once()
            ->with($position, $banner, [])
            ->andReturn('random');

        Assert::same('random', $renderer->render($position));
    }

    public function testMultipleTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $banners = [
            new Banner('1234', 'Main', 0, null, null, null, []),
            new Banner('1235', 'Secondary', 0, null, null, null, []),
        ];
        $position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeMultiple, Position::BreakpointTypeMin, $banners);

        $bannersResolver
            ->shouldReceive('resolveMultiple')
            ->once()
            ->with($position)
            ->andReturn($banners);

        $rendererBridge
            ->shouldReceive('renderMultiple')
            ->once()
            ->with($position, $banners, [])
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position));
    }

    public function testRendererExceptionShouldBeThrownWhenBridgeThrowsTheException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []);

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [])
            ->andThrow(new RendererException('Test exception'));

        Assert::exception(
            static fn () => $renderer->render($position),
            RendererException::class,
            'Test exception',
        );
    }

    public function testRendererExceptionShouldBeThrownWhenBridgeThrowsAnyException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []);

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [])
            ->andThrow(new Exception('Test exception'));

        Assert::exception(
            static fn () => $renderer->render($position),
            RendererException::class,
            'Renderer bridge of type %A% thrown an exception while rendering a position homepage.top: Test exception',
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new RendererTest())->run();
