<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use Exception;
use SixtyEightPublishers\AmpClient\Renderer\OutputBuffer;
use Tester\Assert;
use Tester\TestCase;
use function ob_get_level;

require __DIR__ . '/../bootstrap.php';

final class OutputBufferTest extends TestCase
{
    public function testOutputShouldBeCaptured(): void
    {
        $level = ob_get_level();
        $output = OutputBuffer::capture(static function (): void {
            echo 'Test';
            echo 'Test';
            echo 'Test';
        });

        Assert::same($level, ob_get_level());
        Assert::same('TestTestTest', $output);
    }

    public function testOutputBufferingShouldBeStoppedWhenExceptionIsThrown(): void
    {
        $level = ob_get_level();

        Assert::exception(
            static function (): void {
                OutputBuffer::capture(static function (): void {
                    echo 'Test';
                    throw new Exception('Test error');
                });
            },
            Exception::class,
            'Test error',
        );

        Assert::same($level, ob_get_level());
    }
}

(new OutputBufferTest())->run();
