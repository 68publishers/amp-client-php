<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class RendererConfig
{
    /** @var string|Statement|null */
    public $bridge = null;

    /** @var array<string, string> */
    public array $templates = [];
}
