<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte;

use Latte\Engine;
use function version_compare;

final class AmpClientLatteExtension
{
    private function __construct() {}

    public static function register(Engine $latte, RendererProvider $rendererProvider, string $tagName = 'banner'): void
    {
        if (version_compare(Engine::VERSION, '3', '<')) { // @phpstan-ignore-line
            AmpClientLatte2Extension::register($latte, $rendererProvider, $tagName);
        } else {
            $latte->addExtension(new AmpClientLatte3Extension($rendererProvider, $tagName));
        }
    }
}
