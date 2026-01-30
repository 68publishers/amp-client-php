<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class HttpConfig
{
    /** @var array<string, mixed> */
    public array $guzzle_config = [];

    /** @var list<Statement> */
    public array $middlewares = [];
}
