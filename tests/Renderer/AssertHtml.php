<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use DomDocument;
use Tester\Assert;
use Wa72\HtmlPrettymin\PrettyMin;
use function file_get_contents;

final class AssertHtml
{
    private function __construct() {}

    public static function assert(string $expectationFile, string $actualHtml): void
    {
        $expectedDom = new DomDocument();
        $expectedDom->loadHtml(file_get_contents($expectationFile), LIBXML_NOERROR);

        $expectedHtml = (new PrettyMin(['minify_js' => false, 'minify_css' => false, 'remove_comments' => false]))
            ->load($expectedDom)
            ->minify()
            ->saveHtml();

        $actualDom = new DomDocument();
        $actualDom->loadHtml($actualHtml, LIBXML_NOERROR);

        $actualHtml = (new PrettyMin(['minify_js' => false, 'minify_css' => false, 'remove_comments' => false]))
            ->load($actualDom)
            ->minify()
            ->saveHtml();

        Assert::same($expectedHtml, $actualHtml);
    }
}
