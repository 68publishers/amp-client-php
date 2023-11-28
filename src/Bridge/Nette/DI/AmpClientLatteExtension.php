<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\Bridges\ApplicationDI\LatteExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use RuntimeException;
use SixtyEightPublishers\AmpClient\Bridge\Latte\AmpClientLatteExtension as AmpClientLatteExtensionRegister;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEventHandlerInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\DirectRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingInPresenterContextMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Nette\Application\AttachPresenterHandlersOnApplicationHandler;
use SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config\AmpClientLatteConfig;
use function assert;
use function count;
use function sprintf;

final class AmpClientLatteExtension extends CompilerExtension
{
    private const RenderingModes = [
        'direct' => DirectRenderingMode::class,
        'queued' => QueuedRenderingMode::class,
        'queued_in_presenter_context' => QueuedRenderingInPresenterContextMode::class,
    ];

    private bool $debugMode;

    public function __construct(bool $debugMode = false)
    {
        $this->debugMode = $debugMode;
    }

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'banner_macro_name' => Expect::string('banner'),
            'rendering_mode' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
                ->default('direct'),
        ])->castTo(AmpClientLatteConfig::class);
    }

    public function loadConfiguration(): void
    {
        if (!$this->extensionExists(AmpClientExtension::class)) {
            throw new RuntimeException(sprintf(
                'Compiler extension %s is required for %s.',
                AmpClientExtension::class,
                self::class,
            ));
        }

        if (!$this->extensionExists(LatteExtension::class)) {
            throw new RuntimeException(sprintf(
                'Compiler extension %s is required for %s.',
                LatteExtension::class,
                self::class,
            ));
        }

        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        assert($config instanceof AmpClientLatteConfig);

        $renderingMode = $config->rendering_mode;

        if (is_string($renderingMode) && isset(self::RenderingModes[$renderingMode])) {
            $renderingMode = self::RenderingModes[$renderingMode];
        }

        if (!($renderingMode instanceof Statement)) {
            $renderingMode = new Statement($renderingMode);
        }

        $builder->addDefinition($this->prefix('rendererProvider'))
            ->setAutowired(false)
            ->setFactory(RendererProvider::class)
            ->addSetup('setRenderingMode', [
                'renderingMode' => $renderingMode,
            ])
            ->addSetup('setDebugMode', [
                'debugMode' => $this->debugMode,
            ]);
    }

    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        assert($config instanceof AmpClientLatteConfig);

        $latteFactory = $builder->getDefinition($builder->getByType(Engine::class) ?? 'nette.latteFactory');
        assert($latteFactory instanceof FactoryDefinition);
        $latteFactoryResultDefinition = $latteFactory->getResultDefinition();

        $latteFactoryResultDefinition->addSetup('?::register(?, ?, ?)', [
            ContainerBuilder::literal(AmpClientLatteExtensionRegister::class),
            new Reference('self'),
            new Reference($this->prefix('rendererProvider')),
            $config->banner_macro_name,
        ]);

        $rendererProviderDefinition = $builder->getDefinition($this->prefix('rendererProvider'));
        $configureClientEventHandlers = $builder->findByType(ConfigureClientEventHandlerInterface::class);
        assert($rendererProviderDefinition instanceof ServiceDefinition);

        foreach ($configureClientEventHandlers as $configureClientEventHandler) {
            $rendererProviderDefinition->addSetup('addConfigureClientEventHandler', [
                'handler' => $configureClientEventHandler,
            ]);
        }

        if ($this->extensionExists(ApplicationExtension::class)) {
            $applicationDefinition = $builder->getDefinitionByType(Application::class);
            assert($applicationDefinition instanceof ServiceDefinition);

            $applicationDefinition->addSetup('?::attach(?, ?)', [
                ContainerBuilder::literal(AttachPresenterHandlersOnApplicationHandler::class),
                new Reference('self'),
                new Reference($this->prefix('rendererProvider')),
            ]);
        }
    }

    /**
     * @param class-string $classname
     */
    private function extensionExists(string $classname): bool
    {
        return 0 < count($this->compiler->getExtensions($classname));
    }
}
