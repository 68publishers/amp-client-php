<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use Closure;
use Throwable;
use function ob_start;

final class OutputBuffer
{
    private function __construct() {}

    public static function capture(Closure $function): string
    {
        ob_start(function () {});

        try {
            $function();

            return (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }
    }
}
