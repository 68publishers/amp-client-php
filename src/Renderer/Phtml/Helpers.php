<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Phtml;

use function htmlspecialchars;
use function str_replace;
use function strpbrk;
use function strpos;

final class Helpers
{
    private function __construct() {}

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

    /**
     * @param array<string, mixed> $attributes
     */
    public static function printAttributes(array $attributes): string
    {
        $printed = [];

        foreach ($attributes as $name => $value) {
            if (null === $value || false === $value) {
                continue;
            }

            if (true === $value) {
                $printed[] = $name;

                continue;
            }

            $printed[] = $name . '="' . self::escapeHtmlAttr($value) . '"';
        }

        $attrs = implode(' ', $printed);

        return '' !== $attrs ? (' ' . $attrs) : '';
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     */
    public static function prefixKeys(array $array, string $prefix): array
    {
        $attributes = [];

        foreach ($array as $key => $value) {
            $attributes[$prefix . $key] = $value;
        }

        return $attributes;
    }
}
