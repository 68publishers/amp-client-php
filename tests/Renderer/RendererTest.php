<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use Closure;
use Exception;
use Hamcrest\Matchers;
use Mockery;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Expression\ExpressionParser;
use SixtyEightPublishers\AmpClient\Expression\ExpressionParserInterface;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolverInterface;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
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

        [$bannersResolver, $rendererBridge, $expressionParser] = call_user_func(Closure::bind(static function () use ($renderer): array {
            return [
                $renderer->bannersResolver,
                $renderer->rendererBridge,
                $renderer->expressionParser,
            ];
        }, null, Renderer::class));

        Assert::equal(new BannersResolver(), $bannersResolver);
        Assert::equal(new PhtmlRendererBridge(), $rendererBridge);
        Assert::equal(new ExpressionParser(), $expressionParser);
    }

    public function testNotFoundTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
            ->andReturn('not found');

        Assert::same('not found', $renderer->render($position));
    }

    public function testSingleTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner = new Banner('1234', 'Main', 0, null, null, null, null, []);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeSingle,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
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
            ->with($position, $banner, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
            ->andReturn('single');

        Assert::same('single', $renderer->render($position));
    }

    public function testRandomTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner = new Banner('1234', 'Main', 0, null, null, null, null, []);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeRandom,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
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
            ->with($position, $banner, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
            ->andReturn('random');

        Assert::same('random', $renderer->render($position));
    }

    public function testMultipleTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banners = [
            new Banner('1234', 'Main', 0, null, null, null, null, []),
            new Banner('1235', 'Secondary', 0, null, null, null, null, []),
        ];
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeMultiple,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [
                'fetchpriority' => '0:high,low',
            ],
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
            ->with($position, $banners, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && ['fetchpriority' => '0:high,low', 'loading' => 'lazy'] === $options->toArray();
            }))
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position, [], ['fetchpriority' => 'high', 'loading' => 'lazy']));
    }

    public function testClientSideTemplateShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new RequestPosition('homepage.top', [
            new BannerResource('role', 'vip'),
        ]);

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
            ->andReturn('client-side');

        Assert::same('client-side', $renderer->renderClientSide($position));
    }

    public function testClientSideTemplateWithEmbedModeShouldBeRendered(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new RequestPosition('homepage.top', [
            new BannerResource('role', 'vip'),
        ]);

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::embed()), [], Mockery::on(function ($options): bool {
                return $options instanceof Options && ['omit-default-resources' => '1'] === $options->toArray();
            }))
            ->andReturn('client-side');

        Assert::same('client-side', $renderer->renderClientSide($position, [], [], ClientSideMode::embed()));
    }

    public function testRendererExceptionShouldBeThrownOnRenderingWhenBridgeThrowsTheException(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
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
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new RequestPosition('homepage.top');

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
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
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new ResponsePosition(
            null,
            'homepage.top',
            null,
            0,
            null,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [],
        );

        $rendererBridge
            ->shouldReceive('renderNotFound')
            ->once()
            ->with($position, [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
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
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new RequestPosition('homepage.top');

        $rendererBridge
            ->shouldReceive('renderClientSide')
            ->once()
            ->with($position, Matchers::equalTo(ClientSideMode::managed()), [], Mockery::on(function ($options): bool {
                return $options instanceof Options && [] === $options->toArray();
            }))
            ->andThrow(new Exception('Test exception'));

        Assert::exception(
            static fn () => $renderer->renderClientSide($position),
            RendererException::class,
            'Renderer bridge of type %A% thrown an exception while rendering a position homepage.top: Test exception',
        );
    }

    public function testSingleTemplateShouldBeRenderedWithConditionalAttributesWhenContentFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner = new Banner('1234', 'Main', 0, null, null, null, null, [
            new HtmlContent(null, ''),
            new HtmlContent(500, ''),
        ]);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeSingle,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
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
            ->with(
                $position,
                $banner,
                Mockery::on(function ($attributes): bool {
                    return [
                        'class' => 'base-class exists exists-default exists-500',
                        'data-base' => true,
                        'data-exists' => true,
                        'data-exists-default' => true,
                        'data-exists-500' => true,
                    ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('single');

        Assert::same('single', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    public function testSingleTemplateShouldNotBeRenderedWithConditionalAttributesWhenContentNotFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner = new Banner('1234', 'Main', 0, null, null, null, null, []);
        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeSingle,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
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
            ->with(
                $position,
                $banner,
                Mockery::on(function ($attributes): bool {
                    return [
                            'class' => 'base-class',
                            'data-base' => true,
                        ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('single');

        Assert::same('single', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    public function testSingleTemplateShouldNotBeRenderedWithConditionalAttributesWhenBannerNotFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeSingle,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [],
        );

        $bannersResolver
            ->shouldReceive('resolveSingle')
            ->once()
            ->with($position)
            ->andReturn(null);

        $rendererBridge
            ->shouldReceive('renderSingle')
            ->once()
            ->with(
                $position,
                null,
                Mockery::on(function ($attributes): bool {
                    return [
                            'class' => 'base-class',
                            'data-base' => true,
                        ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('single');

        Assert::same('single', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    public function testMultipleTemplateShouldBeRenderedWithConditionalAttributesWhenContentFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner1 = new Banner('1234', 'Main', 0, null, null, null, null, [
            new HtmlContent(null, ''),
            new HtmlContent(500, ''),
        ]);
        $banner2 = new Banner('1235', 'Secondary', 0, null, null, null, null, [
            new HtmlContent(900, ''),
        ]);

        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeMultiple,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [$banner1, $banner2],
        );

        $bannersResolver
            ->shouldReceive('resolveMultiple')
            ->once()
            ->with($position)
            ->andReturn([$banner1, $banner2]);

        $rendererBridge
            ->shouldReceive('renderMultiple')
            ->once()
            ->with(
                $position,
                [$banner1, $banner2],
                Mockery::on(function ($attributes): bool {
                    return [
                            'class' => 'base-class exists exists-default exists-500 exists-900',
                            'data-base' => true,
                            'data-exists' => true,
                            'data-exists-default' => true,
                            'data-exists-500' => true,
                            'data-exists-900' => true,
                        ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    public function testMultipleTemplateShouldNotBeRenderedWithConditionalAttributesWhenContentNotFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $banner1 = new Banner('1234', 'Main', 0, null, null, null, null, []);
        $banner2 = new Banner('1235', 'Secondary', 0, null, null, null, null, []);

        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeMultiple,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [$banner1, $banner2],
        );

        $bannersResolver
            ->shouldReceive('resolveMultiple')
            ->once()
            ->with($position)
            ->andReturn([$banner1, $banner2]);

        $rendererBridge
            ->shouldReceive('renderMultiple')
            ->once()
            ->with(
                $position,
                [$banner1, $banner2],
                Mockery::on(function ($attributes): bool {
                    return [
                            'class' => 'base-class',
                            'data-base' => true,
                        ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    public function testMultipleTemplateShouldNotBeRenderedWithConditionalAttributesWhenBannerNotFound(): void
    {
        $bannersResolver = Mockery::mock(BannersResolverInterface::class);
        $rendererBridge = Mockery::mock(RendererBridgeInterface::class);
        $expressionParser = Mockery::mock(ExpressionParserInterface::class);
        $renderer = new Renderer($bannersResolver, $rendererBridge, $expressionParser);

        $position = new ResponsePosition(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            ResponsePosition::DisplayTypeMultiple,
            ResponsePosition::BreakpointTypeMin,
            ResponsePosition::ModeManaged,
            null,
            [],
            [],
        );

        $bannersResolver
            ->shouldReceive('resolveMultiple')
            ->once()
            ->with($position)
            ->andReturn([]);

        $rendererBridge
            ->shouldReceive('renderMultiple')
            ->once()
            ->with(
                $position,
                [],
                Mockery::on(function ($attributes): bool {
                    return [
                            'class' => 'base-class',
                            'data-base' => true,
                        ] === $attributes;
                }),
                Mockery::on(function ($options): bool {
                    return $options instanceof Options && [] === $options->toArray();
                }),
            )
            ->andReturn('multiple');

        Assert::same('multiple', $renderer->render($position, [
            'class' => 'base-class',
            'exists@class' => 'exists',
            'exists(default)@class' => 'exists-default',
            'exists(500)@class' => 'exists-500',
            'exists(900)@class' => 'exists-900',
            'data-base' => true,
            'exists@data-exists' => true,
            'exists(default)@data-exists-default' => true,
            'exists(500)@data-exists-500' => true,
            'exists(900)@data-exists-900' => true,
        ]));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new RendererTest())->run();
