<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;
use Nette\Schema\DynamicParameter;

final class AmpClientConfig
{
    /** @var string|DynamicParameter|null */
    public $method = null;

    /** @var string|DynamicParameter */
    public $url;

    /** @var string|DynamicParameter */
    public $channel;

    /** @var int|DynamicParameter|null */
    public $version = null;

    /** @var string|DynamicParameter|null */
    public $locale = null;

    /** @var array<int, Statement> */
    public array $default_resources = [];

    /** @var string|DynamicParameter|null */
    public $origin = null;

    public CacheConfig $cache;

    public HttpConfig $http;

    public RendererConfig $renderer;

    public ClosingConfig $closing;
}
