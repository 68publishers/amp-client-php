<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class CacheConfig
{
    public ?Statement $storage;

    /** @var string|int|null */
    public $expiration = null;

    public ?string $cache_control_header_override = null;
}
