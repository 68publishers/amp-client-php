<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use Closure;
use Exception;
use Hamcrest\Matchers;
use Mockery;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolverInterface;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
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

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], [])
            ->andReturn('not found');

        Assert::same('not found', $renderer->render($position));
    }

    public function testSingleTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $banner = new Banner('1234', 'Main', 0, null, null, null, []);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeSingle,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            [$banner],
        );

        $bannersResolver
            ->shouldReceive('resolveSingle')
            ->once()
            ->with($position)
            ->andReturn($banner);

        $rendererBridge
            ->shouldReceive('renderSingle')
            ->once()
            ->with($position, $banner, [], [])
            ->andReturn('single');

        Assert::same('single', $renderer->render($position));
    }

    public function testRandomTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $banner = new Banner('1234', 'Main', 0, null, null, null, []);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeRandom,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            [$banner],
        );

        $bannersResolver
            ->shouldReceive('resolveRandom')
            ->once()
            ->with($position)
            ->andReturn($banner);

        $rendererBridge
            ->shouldReceive('renderRandom')
            ->once()
            ->with($position, $banner, [], [])
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
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeMultiple,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            $banners,
        );

        $bannersResolver
            ->shouldReceive('resolveMultiple')
            ->once()
            ->with($position)
            ->andReturn($banners);

        $rendererBridge
            ->shouldReceive('renderMultiple')
            ->once()
            ->with($position, $banners, [], [])
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position));
    }

    public function testClientSideTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new RequestPosition('homepage.top', [
            new BannerResource('role', 'vip'),
        ]);

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], [])
            ->andReturn('client-side');

        Assert::same('client-side', $renderer->renderClientSide($position));
    }

    public function testClientSideTemplateWithEmbedModeShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new RequestPosition('homepage.top', [
            new BannerResource('role', 'vip'),
        ]);

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::embed()), [], ['omit-default-resources' => '1'])
            ->andReturn('client-side');

        Assert::same('client-side', $renderer->renderClientSide($position, [], [], ClientSideMode::embed()));
    }

    public function testRendererExceptionShouldBeThrownOnRenderingWhenBridgeThrowsTheException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], [])
            ->andThrow(new RendererException('Test exception'));

        Assert::exception(
            static fn () => $renderer->render($position),
            RendererException::class,
            'Test exception',
        );
    }

    public function testRendererExceptionShouldBeThrownOnClientSideRenderingWhenBridgeThrowsTheException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new RequestPosition('homepage.top');

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], [])
            ->andThrow(new RendererException('Test exception'));

        Assert::exception(
            static fn () => $renderer->renderClientSide($position),
            RendererException::class,
            'Test exception',
        );
    }

    public function testRendererExceptionShouldBeThrownOnRenderingWhenBridgeThrowsAnyException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            new Dimensions(null, null),
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], [])
            ->andThrow(new Exception('Test exception'));

        Assert::exception(
            static fn () => $renderer->render($position),
            RendererException::class,
            'Renderer bridge of type %A% thrown an exception while rendering a position homepage.top: Test exception',
        );
    }

    public function testRendererExceptionShouldBeThrownOnClientSideRenderingWhenBridgeThrowsAnyException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge);

        $position = new RequestPosition('homepage.top');

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], [])
            ->andThrow(new Exception('Test exception'));

        Assert::exception(
            static fn () => $renderer->renderClientSide($position),
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
