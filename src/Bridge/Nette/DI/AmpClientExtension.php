<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI;

use Closure;
use Nette\Bridges\ApplicationDI\LatteExtension;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use RuntimeException;
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\AmpClientConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\CacheConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\HttpConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\RendererConfig;
use SixtyEightPublishers\AmpClient\Bridge\Nette\NetteCacheStorage;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheStorageInterface;
use SixtyEightPublishers\AmpClient\Http\Cache\NoCacheStorage;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactoryInterface;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolverInterface;
use SixtyEightPublishers\AmpClient\Renderer\Latte\ClosureLatteFactory;
use SixtyEightPublishers\AmpClient\Renderer\Latte\LatteRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorHandlerInterface;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydratorInterface;
use function array_filter;
use function array_values;
use function assert;
use function class_exists;
use function count;
use function is_string;
use function sprintf;

final class AmpClientExtension extends CompilerExtension
{
    private const RendererBridges = [
        'phtml' => PhtmlRendererBridge::class,
        'latte' => LatteRendererBridge::class,
    ];

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
            'renderer' => Expect::structure([
                'bridge' => Expect::anyOf(Expect::string(), Expect::type(Statement::class)),
                'templates' => Expect::structure([
                    'single' => Expect::string(),
                    'random' => Expect::string(),
                    'multiple' => Expect::string(),
                    'not_found' => Expect::string(),
                ])->castTo('array'),
            ])->castTo(RendererConfig::class),
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
                'responseHydrator' => new Reference($this->prefix('responseHydrator')),
                'guzzleClientConfig' => $config->http->guzzle_config,
            ]);

        $builder->addDefinition($this->prefix('ampClient'))
            ->setType(AmpClientInterface::class)
            ->setFactory(AmpClient::class, [
                'config' => new Reference($this->prefix('config')),
                'httpClientFactory' => new Reference($this->prefix('httpClientFactory')),
                'cacheStorage' => new Reference($this->prefix('cacheStorage')),
            ]);

        $builder->addDefinition($this->prefix('renderer.rendererBridge'))
            ->setAutowired(false)
            ->setType(RendererBridgeInterface::class)
            ->setFactory($this->resolveRendererBridgeCreator($config->renderer));

        $builder->addDefinition($this->prefix('renderer.bannersResolver'))
            ->setAutowired(false)
            ->setType(BannersResolverInterface::class)
            ->setFactory(BannersResolver::class);

        $builder->addDefinition($this->prefix('renderer'))
            ->setType(RendererInterface::class)
            ->setFactory(Renderer::class, [
                'bannersResolver' => new Reference($this->prefix('renderer.bannersResolver')),
                'rendererBridge' => new Reference($this->prefix('renderer.rendererBridge')),
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

    private function resolveRendererBridgeCreator(RendererConfig $config): Statement
    {
        $rendererBridge = $config->bridge ?? ($this->extensionExists(LatteExtension::class) ? 'latte' : 'phtml');

        if (is_string($rendererBridge) && isset(self::RendererBridges[$rendererBridge])) {
            $rendererBridge = self::RendererBridges[$rendererBridge];
        }

        if (LatteRendererBridge::class === $rendererBridge) {
            if (!$this->extensionExists(LatteExtension::class)) {
                throw new RuntimeException(sprintf(
                    'Renderer of type %s can not be used without the compiler extension of type %s.',
                    LatteRendererBridge::class,
                    LatteExtension::class,
                ));
            }

            $rendererBridge = new Statement(LatteRendererBridge::class, [
                'latteFactory' => new Statement(ClosureLatteFactory::class, [
                    'factory' => new Statement([Closure::class, 'fromCallable'], [
                        [
                            new Reference(class_exists(LatteFactory::class) ? LatteFactory::class : ILatteFactory::class),
                            'create',
                        ],
                    ]),
                ]),
            ]);
        }

        if (!($rendererBridge instanceof Statement)) {
            $rendererBridge = new Statement($rendererBridge);
        }

        $templatesOverride = array_filter([
            Templates::TemplateSingle => $config->templates['single'] ?? null,
            Templates::TemplateRandom => $config->templates['random'] ?? null,
            Templates::TemplateMultiple => $config->templates['multiple'] ?? null,
            Templates::TemplateNotFound => $config->templates['not_found'] ?? null,
        ]);

        if (0 < count($templatesOverride)) {
            $rendererBridge = new Statement([$rendererBridge, 'overrideTemplates'], [
                'templates' => new Statement(Templates::class, [
                    'filesMap' => $templatesOverride,
                ]),
            ]);
        }

        return $rendererBridge;
    }

    /**
     * @param class-string $classname
     */
    private function extensionExists(string $classname): bool
    {
        return 0 < count($this->compiler->getExtensions($classname));
    }
}
