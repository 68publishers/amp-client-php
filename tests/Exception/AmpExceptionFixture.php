<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Exception;

use Exception;
use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;

final class AmpExceptionFixture extends Exception implements AmpExceptionInterface
{
}
