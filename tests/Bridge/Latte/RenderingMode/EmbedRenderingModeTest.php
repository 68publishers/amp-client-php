<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\EmbedRenderingMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class EmbedRenderingModeTest extends TestCase
{
    public static function testModeBehaviour(): void
    {
        $mode = new EmbedRenderingMode();

        $position = new Position('homepage.top');

        Assert::same('embed', $mode->getName());
        Assert::false($mode->supportsQueues());
        Assert::true($mode->shouldBePositionRenderedClientSide($position));
        Assert::false($mode->shouldBePositionQueued($position, (object) []));
    }
}

(new EmbedRenderingModeTest())->run();
