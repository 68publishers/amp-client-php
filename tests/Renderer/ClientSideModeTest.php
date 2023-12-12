<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use InvalidArgumentException;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ClientSideModeTest extends TestCase
{
    public function testManagedModeShouldBeCreated(): void
    {
        $mode = ClientSideMode::managed();

        Assert::same('managed', $mode->getValue());
        Assert::true($mode->isManaged());
        Assert::false($mode->isEmbed());
    }

    public function testManagedModeFromValueShouldBeCreated(): void
    {
        $mode = ClientSideMode::fromValue('managed');

        Assert::same('managed', $mode->getValue());
        Assert::true($mode->isManaged());
        Assert::false($mode->isEmbed());
    }

    public function testEmbedModeShouldBeCreated(): void
    {
        $mode = ClientSideMode::embed();

        Assert::same('embed', $mode->getValue());
        Assert::false($mode->isManaged());
        Assert::true($mode->isEmbed());
    }

    public function testEmbedModeFromValueShouldBeCreated(): void
    {
        $mode = ClientSideMode::fromValue('embed');

        Assert::same('embed', $mode->getValue());
        Assert::false($mode->isManaged());
        Assert::true($mode->isEmbed());
    }

    public function testExceptionShouldBeThrownWhenModeIsCreatedFromInvalidValue(): void
    {
        Assert::exception(
            static fn () => ClientSideMode::fromValue('test'),
            InvalidArgumentException::class,
            'Value "test" is not valid client side mode.',
        );
    }
}

(new ClientSideModeTest())->run();
