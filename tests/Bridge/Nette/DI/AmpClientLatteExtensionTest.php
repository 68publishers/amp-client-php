<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\DI;

use Closure;
use Nette\Application\Application;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Container;
use RuntimeException;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEventHandlerInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\DirectRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingInPresenterContextMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\RenderingModeInterface;
use SixtyEightPublishers\AmpClient\Bridge\Nette\Application\AttachPresenterHandlersOnApplicationHandler;
use SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\Event\ConfigureClientEventHandlerFixture;
use Tester\Assert;
use Tester\TestCase;
use function assert;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.php';

final class AmpClientLatteExtensionTest extends TestCase
{
    public function testExceptionShouldBeThrownWhenAmpClientExtensionIsMissing(): void
    {
        Assert::exception(
            static fn () => ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withMissingAmpClientExtension.neon', ['latte']),
            RuntimeException::class,
            'Compiler extension %A%\\AmpClientExtension is required for %A%\\AmpClientLatteExtension.',
        );
    }

    public function testExceptionShouldBeThrownWhenLatteExtensionIsMissing(): void
    {
        Assert::exception(
            static fn () => ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.minimal.neon'),
            RuntimeException::class,
            'Compiler extension %A%\\LatteExtension is required for %A%\\AmpClientLatteExtension.',
        );
    }

    public function testContainerWithMinimalConfiguration(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.minimal.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithMinimalConfigurationAndApplicationEnabled(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.minimal.neon', ['latte', 'application', 'routing', 'http']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );

        $this->assertApplicationHandlerAttached($container);
    }

    public function testContainerWithDebugMode(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withDebugMode.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            true,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithDirectRenderingModeAsString(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withDirectRenderingModeAsString.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithDirectRenderingModeAsClassname(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withDirectRenderingModeAsClassname.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithDirectRenderingModeAsStatement(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withDirectRenderingModeAsStatement.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingModeAsString(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingModeAsString.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingModeAsClassname(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingModeAsClassname.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingModeAsStatement(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingModeAsStatement.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingInPresenterModeModeAsString(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingInPresenterContextModeAsString.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingInPresenterContextMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingInPresenterModeModeAsClassname(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingInPresenterContextModeAsClassname.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingInPresenterContextMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithQueuedRenderingInPresenterModeModeAsStatement(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withQueuedRenderingInPresenterContextModeAsStatement.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new QueuedRenderingInPresenterContextMode(),
            [
                ConfigureClientEventHandlerInterface::class => [],
            ],
        );
    }

    public function testContainerWithConfigureClientEventHandler(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientLatteExtension/config.withConfigureClientEventHandler.neon', ['latte']);

        $this->assertLatteExtension(
            $container,
            false,
            new DirectRenderingMode(),
            [
                ConfigureClientEventHandlerInterface::class => [
                    new ConfigureClientEventHandlerFixture(null),
                ],
            ],
        );
    }

    private function assertLatteExtension(Container $container, bool $debugMode, RenderingModeInterface $renderingMode, array $eventHandlers): void
    {
        $latteFactory = $container->getByType(class_exists(LatteFactory::class) ? LatteFactory::class : ILatteFactory::class);
        $latte = $latteFactory->create();
        $providers = $latte->getProviders();

        Assert::hasKey('ampClientRenderer', $providers);

        $provider = $providers['ampClientRenderer'];

        Assert::type(RendererProvider::class, $provider);

        call_user_func(Closure::bind(
            static function () use ($provider, $debugMode, $renderingMode, $eventHandlers): void {
                Assert::same($debugMode, $provider->debugMode);
                Assert::equal($renderingMode, $provider->renderingMode);
                Assert::equal($eventHandlers, $provider->eventHandlers);
            },
            null,
            RendererProvider::class,
        ));
    }

    private function assertApplicationHandlerAttached(Container $container): void
    {
        $application = $container->getByType(Application::class);
        assert($application instanceof Application);
        $handler = null;

        foreach ($application->onPresenter as $onPresenterHandler) {
            if ($onPresenterHandler instanceof AttachPresenterHandlersOnApplicationHandler) {
                $handler = $onPresenterHandler;
            }
        }

        Assert::type(AttachPresenterHandlersOnApplicationHandler::class, $handler);
    }
}

(new AmpClientLatteExtensionTest())->run();
