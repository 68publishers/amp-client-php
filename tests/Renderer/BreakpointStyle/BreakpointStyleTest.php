<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer\BreakpointStyle;

use SixtyEightPublishers\AmpClient\Renderer\BreakpointStyle\BreakpointStyle;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class BreakpointStyleTest extends TestCase
{
    public function testStylesShouldBeEmptyInMinModeWhenNoContentsDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, []);

        Assert::same('', $style->getCss());
        Assert::same('', (string) $style);

        Assert::null($style->getMediaForInvisibleContent(null));
    }

    public function testStylesShouldBeEmptyInMaxModeWhenNoContentsDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, []);

        Assert::same('', $style->getCss());
        Assert::same('', (string) $style);

        Assert::null($style->getMediaForInvisibleContent(null));
    }

    public function testStylesShouldBeEmptyInMinModeWhenOnlyDefaultContentDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, [
            new HtmlContent(null, ''),
        ]);

        Assert::same('', $style->getCss());
        Assert::same('', (string) $style);

        Assert::null($style->getMediaForInvisibleContent(null));
    }

    public function testStylesShouldBeEmptyInMaxModeWhenOnlyDefaultContentDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, [
            new HtmlContent(null, ''),
        ]);

        Assert::same('', $style->getCss());
        Assert::same('', (string) $style);

        Assert::null($style->getMediaForInvisibleContent(null));
    }

    public function testStylesOutputInMinModeWhenOnlyContentWithNumericBreakpointDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, [
            new HtmlContent(500, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}@media(min-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(max-width: 499px)', $style->getMediaForInvisibleContent(500));
    }

    public function testStylesOutputInMaxModeWhenOnlyContentWithNumericBreakpointDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, [
            new HtmlContent(500, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}@media(max-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(min-width: 501px)', $style->getMediaForInvisibleContent(500));
    }

    public function testStylesOutputInMinModeWhenMultipleContentsWithNumericBreakpointDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, [
            new HtmlContent(500, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}@media(min-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}}@media(min-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(max-width: 499px), (min-width: 900px)', $style->getMediaForInvisibleContent(500));
        Assert::same('(max-width: 899px)', $style->getMediaForInvisibleContent(900));
    }

    public function testStylesOutputInMaxModeWhenMultipleContentsWithNumericBreakpointDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, [
            new HtmlContent(500, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}@media(max-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}}@media(max-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(min-width: 501px)', $style->getMediaForInvisibleContent(500));
        Assert::same('(min-width: 901px), (max-width: 500px)', $style->getMediaForInvisibleContent(900));
    }

    public function testStylesOutputInMinModeWhenSingleContentWithNumericBreakpointAndDefaultDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, [
            new HtmlContent(null, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}@media(min-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(min-width: 900px)', $style->getMediaForInvisibleContent(null));
        Assert::same('(max-width: 899px)', $style->getMediaForInvisibleContent(900));
    }

    public function testStylesOutputInMaxModeWhenSingleContentWithNumericBreakpointAndDefaultDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, [
            new HtmlContent(null, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}@media(max-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(max-width: 900px)', $style->getMediaForInvisibleContent(null));
        Assert::same('(min-width: 901px)', $style->getMediaForInvisibleContent(900));
    }

    public function testStylesOutputInMinModeWhenMultipleContentsDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMin, [
            new HtmlContent(null, ''),
            new HtmlContent(500, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}@media(min-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}}@media(min-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(min-width: 500px)', $style->getMediaForInvisibleContent(null));
        Assert::same('(max-width: 499px), (min-width: 900px)', $style->getMediaForInvisibleContent(500));
        Assert::same('(max-width: 899px)', $style->getMediaForInvisibleContent(900));
    }

    public function testStylesOutputInMaxModeWhenMultipleContentsDefined(): void
    {
        $style = $this->createBreakpointStyle(Position::BreakpointTypeMax, [
            new HtmlContent(null, ''),
            new HtmlContent(500, ''),
            new HtmlContent(900, ''),
        ]);

        $expected = <<<HTML
<style>[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}@media(max-width: 900px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:block}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:none}}@media(max-width: 500px){[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="default"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="900"]{display:none}[data-amp-banner="homepage.top"] [data-amp-banner-id="12344"] [data-amp-content-breakpoint="500"]{display:block}}</style>
HTML;

        Assert::same($expected, $style->getCss());
        Assert::same($expected, (string) $style);

        Assert::same('(max-width: 900px)', $style->getMediaForInvisibleContent(null));
        Assert::same('(min-width: 501px)', $style->getMediaForInvisibleContent(500));
        Assert::same('(min-width: 901px), (max-width: 500px)', $style->getMediaForInvisibleContent(900));
    }

    private function createBreakpointStyle(string $breakpointType, array $contents): BreakpointStyle
    {
        $banner = new Banner(
            '12344',
            'Main',
            2,
            null,
            null,
            null,
            null,
            $contents,
        );

        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            5,
            Position::DisplayTypeSingle,
            $breakpointType,
            ResponsePosition::ModeManaged,
            null,
            [],
            [$banner],
        );

        return new BreakpointStyle($position, $banner);
    }
}

(new BreakpointStyleTest())->run();
