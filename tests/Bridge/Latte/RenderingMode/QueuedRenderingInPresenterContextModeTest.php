<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\RenderingMode;

use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingInPresenterContextMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class QueuedRenderingInPresenterContextModeTest extends TestCase
{
    public static function testModeBehaviour(): void
    {
        $mode = new QueuedRenderingInPresenterContextMode();

        $position = new Position('homepage.top');

        Assert::same('queued_in_presenter_context', $mode->getName());
        Assert::true($mode->supportsQueues());
        Assert::false($mode->shouldBePositionRenderedClientSide($position));

        Assert::false($mode->shouldBePositionQueued($position, (object) []));
        Assert::true($mode->shouldBePositionQueued($position, (object) [
            'uiPresenter' => new class extends Presenter {},
        ]));
    }
}

(new QueuedRenderingInPresenterContextModeTest())->run();
