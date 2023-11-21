<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\DI;

use Closure;
use Nette\Caching\Storages\MemoryStorage;
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Nette\NetteCacheStorage;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactoryInterface;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.php';

final class AmpClientExtensionTest extends TestCase
{
    public function testContainerWithMinimalConfiguration(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.minimal.neon');
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
    }

    public function testContainerWithMethodOption(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.withMethod.neon');

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withMethod('POST'),
            $client->getConfig(),
        );
    }

    public function testContainerWithVersionOption(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.withVersion.neon');

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withVersion(1),
            $client->getConfig(),
        );
    }

    public function testContainerWithLocaleOption(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.withLocale.neon');

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withLocale('en'),
            $client->getConfig(),
        );
    }

    public function testContainerWithDefaultResourcesOption(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.withDefaultResources.neon');

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
        $client = $this->getClientFromContainer(__DIR__ . '/config.withOrigin.neon');

        Assert::equal(
            ClientConfig::create('https://www.example.com', 'test')
                ->withOrigin('https://www.example.io'),
            $client->getConfig(),
        );
    }

    public function testContainerWithCacheOptions(): void
    {
        $client = $this->getClientFromContainer(__DIR__ . '/config.withCache.neon');

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
        $client = $this->getClientFromContainer(__DIR__ . '/config.withGuzzleConfig.neon');
        $httpClientFactory = $this->unwrapHttpClientFactory($client);

        Assert::type(HttpClientFactory::class, $httpClientFactory);

        $guzzleClientConfig = call_user_func(Closure::bind(static function () use ($httpClientFactory): array {
            return $httpClientFactory->guzzleClientConfig;
        }, null, HttpClientFactory::class));

        Assert::same([
            'custom_option' => 'custom_value',
        ], $guzzleClientConfig);
    }

    private function getClientFromContainer(string $configFile): AmpClient
    {
        $container = ContainerFactory::create($configFile);
        $client = $container->getByType(AmpClientInterface::class);

        Assert::type(AmpClient::class, $client);

        return $client;
    }

    private function unwrapHttpClientFactory(AmpClient $ampClient): HttpClientFactoryInterface
    {
        return call_user_func(Closure::bind(static function () use ($ampClient): HttpClientFactoryInterface {
            return $ampClient->httpClientFactory;
        }, null, AmpClient::class));
    }
}

(new AmpClientExtensionTest())->run();
