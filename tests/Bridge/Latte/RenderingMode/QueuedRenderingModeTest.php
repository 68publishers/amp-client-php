<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class QueuedRenderingModeTest extends TestCase
{
    public static function testModeBehaviour(): void
    {
        $mode = new QueuedRenderingMode();

        $position = new Position('homepage.top');

        Assert::same('queued', $mode->getName());
        Assert::true($mode->supportsQueues());
        Assert::false($mode->shouldBePositionRenderedClientSide($position));
        Assert::true($mode->shouldBePositionQueued($position, (object) []));
    }
}

(new QueuedRenderingModeTest())->run();
