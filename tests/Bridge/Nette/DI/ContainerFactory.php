<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\DI;

use Closure;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Tester\Helpers;
use function sys_get_temp_dir;
use function uniqid;

final class ContainerFactory
{
    private function __construct() {}

    /**
     * @param string|array<string> $configFiles
     */
    public static function create($configFiles, ?Closure $beforeContainerCreated = null): Container
    {
        $tempDir = sys_get_temp_dir() . '/' . uniqid('68publishers:AmpClientPhp', true);

        Helpers::purge($tempDir);

        $configurator = new Configurator();
        $configurator->setTempDirectory($tempDir);
        $configurator->setDebugMode(false);
        $configurator->addStaticParameters([
            'resources' => __DIR__ . '/../../../resources',
        ]);

        foreach ((array) $configFiles as $configFile) {
            $configurator->addConfig($configFile);
        }

        if (null !== $beforeContainerCreated) {
            $beforeContainerCreated($configurator);
        }

        return $configurator->createContainer();
    }
}
