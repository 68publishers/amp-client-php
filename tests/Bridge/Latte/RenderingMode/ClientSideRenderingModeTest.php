<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\ClientSideRenderingMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class ClientSideRenderingModeTest extends TestCase
{
    public static function testModeBehaviour(): void
    {
        $mode = new ClientSideRenderingMode();

        $position = new Position('homepage.top');

        Assert::same('client_side', $mode->getName());
        Assert::false($mode->supportsQueues());
        Assert::true($mode->shouldBePositionRenderedClientSide($position));
        Assert::false($mode->shouldBePositionQueued($position, (object) []));
    }
}

(new ClientSideRenderingModeTest())->run();
