<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\Latte;

use Latte\Engine;
use SixtyEightPublishers\AmpClient\Renderer\Latte\ClosureLatteFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ClosureLatteFactoryTest extends TestCase
{
    public function testClosureShouldBeCalled(): void
    {
        $counter = 0;
        $latte = new Engine();

        $factory = new ClosureLatteFactory(static function () use ($latte, &$counter): Engine {
            ++$counter;

            return $latte;
        });

        Assert::same($latte, $factory->create());
        Assert::same($latte, $factory->create());
        Assert::same(2, $counter);
    }
}

(new ClosureLatteFactoryTest())->run();
