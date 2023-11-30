<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\DirectRenderingMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class DirectRenderingModeTest extends TestCase
{
    public static function testModeBehaviour(): void
    {
        $mode = new DirectRenderingMode();

        $position = new Position('homepage.top');

        Assert::same('direct', $mode->getName());
        Assert::false($mode->supportsQueues());
        Assert::false($mode->shouldBePositionRenderedClientSide($position));
        Assert::false($mode->shouldBePositionQueued($position, (object) []));
    }
}

(new DirectRenderingModeTest())->run();
