<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\DI;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Tester\Helpers;
use function array_keys;
use function sys_get_temp_dir;
use function uniqid;

final class ContainerFactory
{
    private function __construct() {}

    /**
     * @param string|array<string> $configFiles
     * @param array<string>        $defaultExtensions
     */
    public static function create($configFiles, array $defaultExtensions = []): Container
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

        foreach (array_keys($configurator->defaultExtensions) as $extensionName) {
            if (in_array($extensionName, ['di', 'extensions'], true)) { # keep required extensions
                continue;
            }

            if (!in_array($extensionName, $defaultExtensions, true)) {
                unset($configurator->defaultExtensions[$extensionName]);
            }
        }

        return $configurator->createContainer();
    }
}
