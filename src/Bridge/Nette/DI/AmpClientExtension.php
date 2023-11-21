<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\AmpClientConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\CacheConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\HttpConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\NetteCacheStorage;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactoryInterface;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorHandlerInterface;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;
use function array_values;
use function assert;
use function count;

final class AmpClientExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'method' => Expect::anyOf(...ClientConfig::Methods)
                ->dynamic(),
            'url' => Expect::string()
                ->required()
                ->dynamic(),
            'channel' => Expect::string()
                ->required()
                ->dynamic(),
            'version' => Expect::anyOf(...ClientConfig::Versions)
                ->dynamic(),
            'locale' => Expect::string()
                ->nullable()
                ->dynamic(),
            'default_resources' => Expect::arrayOf(
                Expect::anyOf(Expect::string(), Expect::listOf('string')),
                Expect::string(),
            ),
            'origin' => Expect::string()
                ->nullable()
                ->dynamic(),
            'cache' => Expect::structure([
                'storage' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
                    ->nullable()
                    ->before(static function ($factory): Statement {
                        return $factory instanceof Statement ? $factory : new Statement($factory);
                    }),
                'expiration' => Expect::anyOf(Expect::string(), Expect::int())->dynamic(),
                'cache_control_header_override' => Expect::string()
                    ->nullable(),
            ])->castTo(CacheConfig::class),
            'http' => Expect::structure([
                'guzzle_config' => Expect::array(),
            ])->castTo(HttpConfig::class),
        ])->castTo(AmpClientConfig::class);
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        assert($config instanceof AmpClientConfig);

        $builder->addDefinition($this->prefix('config'))
            ->setAutowired(false)
            ->setType(ClientConfig::class)
            ->setFactory($this->createClientConfigCreator($config));

        $cacheStorageCreator = null === $config->cache->storage
            ? new Statement(NoCacheStorage::class)
            : new Statement(NetteCacheStorage::class, [
                'storage' => $config->cache->storage,
            ]);

        $builder->addDefinition($this->prefix('cacheStorage'))
            ->setAutowired(false)
            ->setType(CacheStorageInterface::class)
            ->setFactory($cacheStorageCreator);

        $builder->addDefinition($this->prefix('responseHydrator'))
            ->setAutowired(false)
            ->setType(ResponseHydratorInterface::class)
            ->setFactory(ResponseHydrator::class);

        $builder->addDefinition($this->prefix('responseHydrator.handler.bannersRequest'))
            ->setAutowired(false)
            ->setType(ResponseHydratorHandlerInterface::class)
            ->setFactory(BannersResponseHydratorHandler::class);

        $builder->addDefinition($this->prefix('httpClientFactory'))
            ->setAutowired(false)
            ->setType(HttpClientFactoryInterface::class)
            ->setFactory(HttpClientFactory::class, [
                'responseHydrator' => $this->prefix('@responseHydrator'),
                'guzzleClientConfig' => $config->http->guzzle_config,
            ]);

        $builder->addDefinition($this->prefix('ampClient'))
            ->setType(AmpClientInterface::class)
            ->setFactory(AmpClient::class, [
                'config' => $this->prefix('@config'),
                'httpClientFactory' => $this->prefix('@httpClientFactory'),
                'cacheStorage' => $this->prefix('@cacheStorage'),
            ]);
    }

    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();

        $responseHydratorHandlers = $builder->findByType(ResponseHydratorHandlerInterface::class);
        $responseHydratorService = $builder->getDefinition($this->prefix('responseHydrator'));
        assert($responseHydratorService instanceof ServiceDefinition);

        $responseHydratorService->setArgument('handlers', array_values($responseHydratorHandlers));
    }

    private function createClientConfigCreator(AmpClientConfig $config): Statement
    {
        $clientConfigFactory = new Statement([ClientConfig::class, 'create'], [
            'url' => $config->url,
            'channel' => $config->channel,
        ]);

        if (null !== $config->method) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withMethod'], [
                'method' => $config->method,
            ]);
        }

        if (null !== $config->version) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withVersion'], [
                'version' => $config->version,
            ]);
        }

        if (null !== $config->locale) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withLocale'], [
                'locale' => $config->locale,
            ]);
        }

        if (0 < count($config->default_resources)) {
            $defaultResources = [];

            foreach ($config->default_resources as $resourceCode => $resourceValues) {
                $defaultResources[] = new Statement(BannerResource::class, [$resourceCode, $resourceValues]);
            }

            $clientConfigFactory = new Statement([$clientConfigFactory, 'withDefaultResources'], [
                'resources' => $defaultResources,
            ]);
        }

        if (null !== $config->origin) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withOrigin'], [
                'origin' => $config->origin,
            ]);
        }

        if (null !== $config->cache->expiration) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withCacheExpiration'], [
                'cacheExpiration' => $config->cache->expiration,
            ]);
        }

        if (null !== $config->cache->cache_control_header_override) {
            $clientConfigFactory = new Statement([$clientConfigFactory, 'withCacheControlHeaderOverride'], [
                'cacheControlHeaderOverride' => $config->cache->cache_control_header_override,
            ]);
        }

        return $clientConfigFactory;
    }
}
