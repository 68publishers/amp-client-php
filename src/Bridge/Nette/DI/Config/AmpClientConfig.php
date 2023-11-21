<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class AmpClientConfig
{
    public ?string $method = null;

    public string $url;

    public string $channel;

    public ?int $version = null;

    public ?string $locale = null;

    /** @var array<int, Statement> */
    public array $default_resources = [];

    public ?string $origin = null;

    public CacheConfig $cache;

    public HttpConfig $http;
}
