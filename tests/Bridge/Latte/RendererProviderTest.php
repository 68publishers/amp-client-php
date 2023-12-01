<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte;

use InvalidArgumentException;
use Mockery;
use Psr\Log\LoggerInterface;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEvent;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\RenderingModeInterface;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\Event\ConfigureClientEventHandlerFixture;
use SixtyEightPublishers\AmpClient\Tests\Exception\AmpExceptionFixture;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class RendererProviderTest extends TestCase
{
    public function testInvokingDefaultInstanceWithoutResources(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        new RequestPosition('homepage.top'),
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andReturn('<homepage.top>');

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top'));
    }

    public function testInvokingDefaultInstanceWithSameModeAsDefault(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        new RequestPosition('homepage.top'),
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andReturn('<homepage.top>');

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top', ['mode' => 'direct']));
    }

    public function testInvokingDefaultInstanceWithAttributes(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        new RequestPosition('homepage.top'),
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, ['class' => 'my-custom-class'], [])
            ->andReturn('<homepage.top>');

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top', ['attributes' => ['class' => 'my-custom-class']]));
    }

    public function testInvokingDefaultInstanceWithOptions(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        new RequestPosition('homepage.top'),
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], ['loading' => 'lazy', 'custom' => 'value'])
            ->andReturn('<homepage.top>');

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top', ['options' => ['loading' => 'lazy', 'custom' => 'value']]));
    }

    public function testInvokingDefaultInstanceWithResources(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        new RequestPosition('homepage.top', [
                            new BannerResource('resource1', ['a']),
                            new BannerResource('resource2', ['a', 'b']),
                        ]),
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andReturn('<homepage.top>');

        Assert::same(
            '<homepage.top>',
            $provider(
                new stdClass(),
                'homepage.top',
                [
                    'resources' => [
                        'resource1' => 'a',
                        new BannerResource('resource2', ['a', 'b']),
                    ],
                ],
            ),
        );
    }

    public function testClientConfigurationEventsShouldBeInvokedBeforeFirstFetch(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $modifiedClient = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);

        $handler = new ConfigureClientEventHandlerFixture(static function (ConfigureClientEvent $event) use ($client, $modifiedClient) {
            Assert::same($client, $event->getClient());

            return $event->withClient($modifiedClient);
        });

        $provider->addConfigureClientEventHandler($handler);

        $modifiedClient
            ->shouldReceive('fetchBanners')
            ->twice()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturn(new BannersResponse([
                'homepage.top' => $responsePosition,
            ]));

        $renderer
            ->shouldReceive('render')
            ->twice()
            ->with($responsePosition, [], [])
            ->andReturn('<homepage.top>');

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top'));
        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top'));
        Assert::same(1, $handler->invokedCount);
    }

    public function testExceptionShouldBeThrownWhenClientThrowsExceptionInDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $provider->setDebugMode(true);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andThrow(new AmpExceptionFixture('Test client exception'));

        Assert::exception(
            static fn () => $provider(new stdClass(), 'homepage.top'),
            AmpExceptionFixture::class,
            'Test client exception',
        );
    }

    public function testEmptyStringShouldBeReturnedWhenClientThrowsExceptionInNonDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andThrow(new AmpExceptionFixture('Test client exception'));

        Assert::same('', $provider(new stdClass(), 'homepage.top'));
    }

    public function testExceptionShouldBeLoggedWhenClientThrowsExceptionInNonDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $exception = new AmpExceptionFixture('Test client exception');
        $provider = new RendererProvider($client, $renderer, $logger);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andThrow($exception);

        $logger
            ->shouldReceive('error')
            ->once()
            ->with('Test client exception', ['exception' => $exception]);

        Assert::same('', $provider(new stdClass(), 'homepage.top'));
    }

    public function testEmptyStringShouldBeReturnedWhenPositionIsMissingInResponse(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturn(new BannersResponse([]));

        Assert::same('', $provider(new stdClass(), 'homepage.top'));
    }

    public function testExceptionShouldBeThrownWhenRendererThrowsExceptionInDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $provider->setDebugMode(true);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturn(new BannersResponse([
                'homepage.top' => $responsePosition,
            ]));

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andThrow(new RendererException('Test renderer exception'));

        Assert::exception(
            static fn () => $provider(new stdClass(), 'homepage.top'),
            RendererException::class,
            'Test renderer exception',
        );
    }

    public function testEmptyStringShouldBeReturnedWhenRendererThrowsExceptionInNonDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $provider = new RendererProvider($client, $renderer);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturn(new BannersResponse([
                'homepage.top' => $responsePosition,
            ]));

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andThrow(new RendererException('Test renderer exception'));

        Assert::same('', $provider(new stdClass(), 'homepage.top'));
    }

    public function testExceptionShouldBeLoggedWhenRendererThrowsExceptionInNonDebugMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $exception = new RendererException('Test renderer exception');
        $provider = new RendererProvider($client, $renderer, $logger);

        $responsePosition = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturn(new BannersResponse([
                'homepage.top' => $responsePosition,
            ]));

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition, [], [])
            ->andThrow($exception);

        $logger
            ->shouldReceive('error')
            ->once()
            ->with('Test renderer exception', ['exception' => $exception]);

        Assert::same('', $provider(new stdClass(), 'homepage.top'));
    }

    public function testPositionsShouldBeQueuedAndReplacedInStringOutput(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $renderingMode = Mockery::mock(RenderingModeInterface::class);
        $globals = new stdClass();
        $requestPosition1 = new RequestPosition('homepage.top');
        $requestPosition2 = new RequestPosition('homepage.bottom', [
            new BannerResource('resource', 'a'),
        ]);

        $provider = new RendererProvider($client, $renderer);

        $provider->setRenderingMode($renderingMode);

        $renderingMode
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition1): bool {
                Assert::equal($requestPosition1, $position);

                return false;
            })
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition2): bool {
                Assert::equal($requestPosition2, $position);

                return false;
            })
            ->shouldReceive('shouldBePositionQueued')
            ->once()
            ->with(Mockery::type(RequestPosition::class), $globals)
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition1): bool {
                Assert::equal($requestPosition1, $position);

                return true;
            })
            ->shouldReceive('shouldBePositionQueued')
            ->once()
            ->with(Mockery::type(RequestPosition::class), $globals)
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition2): bool {
                Assert::equal($requestPosition2, $position);

                return true;
            });

        Assert::same('<!--AMP_POSITION:homepage.top-->', $provider($globals, 'homepage.top', ['attributes' => ['class' => 'my-custom-class']]));
        Assert::same('<!--AMP_POSITION:homepage.bottom-->', $provider($globals, 'homepage.bottom', ['resources' => ['resource' => ['a']]]));
        Assert::true($provider->isAnythingQueued());

        $responsePosition1 = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $responsePosition2 = new ResponsePosition('1235', 'homepage.bottom', 'Homepage bottom', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition1,
            'homepage.bottom' => $responsePosition2,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response, $requestPosition1, $requestPosition2): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        $requestPosition1,
                        $requestPosition2,
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition1, ['class' => 'my-custom-class'], [])
            ->andReturn('<homepage.top>')
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition2, [], [])
            ->andReturn('<homepage.bottom>');

        Assert::same(
            '<div><homepage.top></div><div><homepage.bottom></div>',
            $provider->renderQueuedPositions('<div><!--AMP_POSITION:homepage.top--></div><div><!--AMP_POSITION:homepage.bottom--></div>'),
        );
    }

    public function testPositionsShouldBeQueuedAndReplacedInArrayOutput(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $renderingMode = Mockery::mock(RenderingModeInterface::class);
        $globals = new stdClass();
        $requestPosition1 = new RequestPosition('homepage.top');
        $requestPosition2 = new RequestPosition('homepage.bottom', [
            new BannerResource('resource', 'a'),
        ]);

        $provider = new RendererProvider($client, $renderer);

        $provider->setRenderingMode($renderingMode);

        $renderingMode
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition1): bool {
                Assert::equal($requestPosition1, $position);

                return false;
            })
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition2): bool {
                Assert::equal($requestPosition2, $position);

                return false;
            })
            ->shouldReceive('shouldBePositionQueued')
            ->once()
            ->with(Mockery::type(RequestPosition::class), $globals)
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition1): bool {
                Assert::equal($requestPosition1, $position);

                return true;
            })
            ->shouldReceive('shouldBePositionQueued')
            ->once()
            ->with(Mockery::type(RequestPosition::class), $globals)
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition2): bool {
                Assert::equal($requestPosition2, $position);

                return true;
            });

        Assert::same('<!--AMP_POSITION:homepage.top-->', $provider($globals, 'homepage.top', ['attributes' => ['class' => 'my-custom-class']]));
        Assert::same('<!--AMP_POSITION:homepage.bottom-->', $provider($globals, 'homepage.bottom', ['resources' => ['resource' => ['a']]]));
        Assert::true($provider->isAnythingQueued());

        $responsePosition1 = new ResponsePosition('1234', 'homepage.top', 'Homepage top', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $responsePosition2 = new ResponsePosition('1235', 'homepage.bottom', 'Homepage bottom', 0, ResponsePosition::DisplayTypeSingle, ResponsePosition::BreakpointTypeMin, []);
        $response = new BannersResponse([
            'homepage.top' => $responsePosition1,
            'homepage.bottom' => $responsePosition2,
        ]);

        $client
            ->shouldReceive('fetchBanners')
            ->once()
            ->with(Mockery::type(BannersRequest::class))
            ->andReturnUsing(static function (BannersRequest $request) use ($response, $requestPosition1, $requestPosition2): BannersResponse {
                Assert::equal(
                    new BannersRequest([
                        $requestPosition1,
                        $requestPosition2,
                    ]),
                    $request,
                );

                return $response;
            });

        $renderer
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition1, ['class' => 'my-custom-class'], [])
            ->andReturn('<homepage.top>')
            ->shouldReceive('render')
            ->once()
            ->with($responsePosition2, [], [])
            ->andReturn('<homepage.bottom>');

        Assert::same(
            [
                '<div><homepage.top></div><div><homepage.bottom></div>',
                '<span>nothing</span>',
                '<p><homepage.bottom></p>',
            ],
            $provider->renderQueuedPositions([
                '<div><!--AMP_POSITION:homepage.top--></div><div><!--AMP_POSITION:homepage.bottom--></div>',
                '<span>nothing</span>',
                '<p><!--AMP_POSITION:homepage.bottom--></p>',
            ]),
        );
    }

    public function testPositionShouldBeRenderedClientSide(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $clientSideRenderingMode = Mockery::mock(RenderingModeInterface::class);
        $requestPosition = new RequestPosition('homepage.top');

        $provider = new RendererProvider($client, $renderer);

        $provider->setRenderingMode($clientSideRenderingMode);

        $clientSideRenderingMode
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition): bool {
                Assert::equal($requestPosition, $position);

                return true;
            });

        $renderer
            ->shouldReceive('renderClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class), [], [])
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition): string {
                Assert::equal($requestPosition, $position);

                return '<homepage.top>';
            });

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top'));
    }

    public function testPositionShouldBeRenderedClientSideWithAlternativeMode(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);
        $clientSideRenderingMode = Mockery::mock(RenderingModeInterface::class);
        $requestPosition = new RequestPosition('homepage.top');

        $provider = new RendererProvider($client, $renderer);

        $clientSideRenderingMode
            ->shouldReceive('getName')
            ->once()
            ->withNoArgs()
            ->andReturn('client_side');

        $clientSideRenderingMode
            ->shouldReceive('shouldBePositionRenderedClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class))
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition): bool {
                Assert::equal($requestPosition, $position);

                return true;
            });

        $renderer
            ->shouldReceive('renderClientSide')
            ->once()
            ->with(Mockery::type(RequestPosition::class), [], [])
            ->andReturnUsing(static function (RequestPosition $position) use ($requestPosition): string {
                Assert::equal($requestPosition, $position);

                return '<homepage.top>';
            });

        $provider->setAlternativeRenderingModes([$clientSideRenderingMode]);

        Assert::same('<homepage.top>', $provider(new stdClass(), 'homepage.top', ['mode' => 'client_side']));
    }

    public function testExceptionShouldBeThrownWhenProviderIsInvokedWithModeThatIsNotRegisteredBetweenAlternativeModes(): void
    {
        $client = Mockery::mock(AmpClientInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);

        $provider = new RendererProvider($client, $renderer);

        Assert::exception(
            static fn () => $provider(new stdClass(), 'homepage.top', ['mode' => 'test']),
            InvalidArgumentException::class,
            'Invalid value for option "mode". The value "test" is not registered between alternative rendering modes.',
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new RendererProviderTest())->run();
