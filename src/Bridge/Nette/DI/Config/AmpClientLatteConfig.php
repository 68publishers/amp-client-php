<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class AmpClientLatteConfig
{
    public string $banner_macro_name;

    public Statement $rendering_mode;
}
