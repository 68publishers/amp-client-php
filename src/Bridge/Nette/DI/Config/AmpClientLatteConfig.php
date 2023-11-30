<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class AmpClientLatteConfig
{
    public string $banner_macro_name;

    /** @var string|Statement */
    public $rendering_mode;

    /** @var array<string|Statement> */
    public array $alternative_rendering_modes = [];
}
