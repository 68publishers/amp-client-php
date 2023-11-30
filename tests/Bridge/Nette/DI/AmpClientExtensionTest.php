<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\DI;

use Closure;
use Nette\Caching\Storages\MemoryStorage;
use Nette\DI\Container;
use RuntimeException;
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Nette\NetteCacheStorage;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactoryInterface;
use SixtyEightPublishers\AmpClient\Renderer\Latte\LatteRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use Tester\Assert;
use Tester\TestCase;
use function assert;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.php';

final class AmpClientExtensionTest extends TestCase
{
    public function testContainerWithMinimalConfiguration(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.minimal.neon', ['latte']);

        $client = $this->getClientFromContainer($container);
        $httpClientFactory = $this->unwrapHttpClientFactory($client);

        Assert::equal(ClientConfig::create('https://www.example.com', 'test'), $client->getConfig());
        Assert::type(NoCacheStorage::class, $client->getCacheStorage());

        Assert::equal(
            new HttpClientFactory(
                new ResponseHydrator([
                    new BannersResponseHydratorHandler(),
                ]),
                [],
            ),
            $httpClientFactory,
        );

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(LatteRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithoutLatteExtension(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.minimal.neon');

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(PhtmlRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithPhtmlRenderer(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withPhtmlRenderer.neon', ['latte']);

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(PhtmlRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithPhtmlRendererAsStatement(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withPhtmlRendererAsStatement.neon', ['latte']);

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(PhtmlRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithLatteRenderer(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withLatteRenderer.neon', ['latte']);

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(LatteRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithLatteRendererAsStatement(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withLatteRendererAsStatement.neon', ['latte']);

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(LatteRendererBridge::class, $rendererBridge);
    }

    public function testContainerWithLatteRendererAsStatementAndMissingLatteExtension(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withLatteRendererAsStatement.neon');

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);

        Assert::type(LatteRendererBridge::class, $rendererBridge);
    }

    public function testExceptionShouldBeThrownWhenLatteRendererIsExplicitlyConfiguredButLatteExtensionMissing(): void
    {
        Assert::exception(
            static fn () => ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withLatteRenderer.neon'),
            RuntimeException::class,
            'Renderer of type %A%\\LatteRendererBridge can not be used without the compiler extension of type Nette\\Bridges\\ApplicationDI\\LatteExtension.',
        );
    }

    public function testContainerWithRendererTemplates(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withRendererTemplates.neon', ['latte']);

        $renderer = $this->getRendererFromContainer($container);
        $rendererBridge = $this->unwrapRendererBridge($renderer);
        $templates = call_user_func(Closure::bind(static fn (): Templates => $rendererBridge->templates, null, PhtmlRendererBridge::class));
        assert($templates instanceof Templates);

        Assert::same(__DIR__ . '/../../../resources/renderer/single/templates/single1.phtml', $templates->getTemplateFile(Templates::Single));
        Assert::same(__DIR__ . '/../../../resources/renderer/random/templates/random1.phtml', $templates->getTemplateFile(Templates::Random));
        Assert::same(__DIR__ . '/../../../resources/renderer/multiple/templates/multiple1.phtml', $templates->getTemplateFile(Templates::Multiple));
        Assert::same(__DIR__ . '/../../../resources/renderer/not-found/templates/not-found1.phtml', $templates->getTemplateFile(Templates::NotFound));
        Assert::same(__DIR__ . '/../../../resources/renderer/client-side/templates/client-side1.phtml', $templates->getTemplateFile(Templates::ClientSide));
    }

    public function testContainerWithMethodOption(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withMethod.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withMethod('POST'),
            $client->getConfig(),
        );
    }

    public function testContainerWithVersionOption(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withVersion.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withVersion(1),
            $client->getConfig(),
        );
    }

    public function testContainerWithLocaleOption(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withLocale.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withLocale('en'),
            $client->getConfig(),
        );
    }

    public function testContainerWithDefaultResourcesOption(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withDefaultResources.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withDefaultResources([
                    new BannerResource('first', ['a']),
                    new BannerResource('second', ['a', 'b']),
                ]),
            $client->getConfig(),
        );
    }

    public function testContainerWithOriginOption(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withOrigin.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withOrigin('https://www.example.io'),
            $client->getConfig(),
        );
    }

    public function testContainerWithCacheOptions(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withCache.neon');
        $client = $this->getClientFromContainer($container);

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withCacheExpiration('+1 hour')
                ->withCacheControlHeaderOverride('no-store'),
            $client->getConfig(),
        );

        Assert::equal(
            new NetteCacheStorage(
                new MemoryStorage(),
            ),
            $client->getCacheStorage(),
        );
    }

    public function testContainerWithGuzzleConfig(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/Config/AmpClientExtension/config.withGuzzleConfig.neon');
        $client = $this->getClientFromContainer($container);
        $httpClientFactory = $this->unwrapHttpClientFactory($client);

        Assert::type(HttpClientFactory::class, $httpClientFactory);

        $guzzleClientConfig = call_user_func(Closure::bind(static function () use ($httpClientFactory): array {
            return $httpClientFactory->guzzleClientConfig;
        }, null, HttpClientFactory::class));

        Assert::same([
            'custom_option' => 'custom_value',
        ], $guzzleClientConfig);
    }

    private function getClientFromContainer(Container $container): AmpClient
    {
        $client = $container->getByType(AmpClientInterface::class);

        Assert::type(AmpClient::class, $client);

        return $client;
    }

    private function getRendererFromContainer(Container $container): Renderer
    {
        $renderer = $container->getByType(RendererInterface::class);

        Assert::type(Renderer::class, $renderer);

        return $renderer;
    }

    private function unwrapHttpClientFactory(AmpClient $ampClient): HttpClientFactoryInterface
    {
        return call_user_func(Closure::bind(static function () use ($ampClient): HttpClientFactoryInterface {
            return $ampClient->httpClientFactory;
        }, null, AmpClient::class));
    }

    private function unwrapRendererBridge(Renderer $renderer): RendererBridgeInterface
    {
        return call_user_func(Closure::bind(static function () use ($renderer): RendererBridgeInterface {
            return $renderer->rendererBridge;
        }, null, Renderer::class));
    }
}

(new AmpClientExtensionTest())->run();
