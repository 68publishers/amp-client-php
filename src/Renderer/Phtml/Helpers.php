<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Phtml;

use function htmlspecialchars;
use function str_replace;
use function strpbrk;
use function strpos;

final class Helpers
{
    /**
     * @param mixed $string
     */
    public static function escapeHtml($string, bool $double = true): string
    {
        return htmlspecialchars((string) $string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', $double);
    }

    /**
     * @param mixed $string
     */
    public static function escapeHtmlAttr($string, bool $double = true): string
    {
        $string = (string) $string;

        if (false !== strpos($string, '`') && false === strpbrk($string, ' <>"\'')) {
            $string .= ' ';
        }

        $string = self::escapeHtml($string, $double);

        return str_replace('{', '&#123;', $string);
    }
}
