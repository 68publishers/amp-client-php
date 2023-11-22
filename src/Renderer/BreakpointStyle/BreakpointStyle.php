<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\BreakpointStyle;

use SixtyEightPublishers\AmpClient\Renderer\Phtml\Helpers;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use function array_map;
use function count;
use function implode;
use function krsort;
use function ksort;
use function sprintf;

final class BreakpointStyle
{
    /** @var array<int, Selector> */
    private array $selectors = [];

    /** @var array<int, Media> */
    private array $media = [];

    public function __construct(Position $position, Banner $banner)
    {
        $selectorMask = sprintf(
            '[data-amp-banner="%s"] [data-amp-content-breakpoint="%s"]',
            Helpers::escapeHtmlAttr($position->getCode()),
            '%s',
        );

        $defaultContent = null;
        $alternativeContents = [];

        foreach ($banner->getContents() as $content) {
            if (null === $content->getBreakpoint()) {
                $defaultContent = $content;
            } else {
                $alternativeContents[$content->getBreakpoint()] = $content;
            }
        }

        if (Position::BreakpointTypeMax === $position->getBreakpointType()) {
            $mediaRuleMask = 'max-width: %dpx';
            krsort($alternativeContents);
        } else {
            $mediaRuleMask = 'min-width: %dpx';
            ksort($alternativeContents);
        }

        foreach ($alternativeContents as $alternativeContent) {
            $this->selectors[] = $selector = new Selector(sprintf(
                $selectorMask,
                $alternativeContent->getBreakpoint(),
            ));

            $selector->properties[] = new Property('display', 'none');

            $this->media[] = $media = new Media(sprintf(
                $mediaRuleMask,
                $alternativeContent->getBreakpoint(),
            ));

            if (null !== $defaultContent) {
                $media->selectors[] = $selectorInMedia = new Selector(sprintf(
                    $selectorMask,
                    'default',
                ));

                $selectorInMedia->properties[] = new Property('display', 'none');
            }

            foreach ($alternativeContents as $alternativeContentInner) {
                $media->selectors[] = $selectorInMedia = new Selector(sprintf(
                    $selectorMask,
                    $alternativeContentInner->getBreakpoint(),
                ));

                $selectorInMedia->properties[] = new Property('display', $alternativeContentInner === $alternativeContent ? 'block' : 'none');
            }
        }
    }

    public function __toString(): string
    {
        return $this->getCss();
    }

    public function getCss(): string
    {
        if (0 >= count($this->selectors) && 0 >= count($this->media)) {
            return '';
        }

        $styles = [];

        foreach ($this->selectors as $selector) {
            $styles[] = $this->stringifySelector($selector);
        }

        foreach ($this->media as $media) {
            $styles[] = $this->stringifyMedia($media);
        }

        return '<style>' . implode('', $styles) . '</style>';
    }

    private function stringifySelector(Selector $selector): string
    {
        $properties = array_map(
            static fn (Property $property): string => $property->name . ':' . $property->value,
            $selector->properties,
        );

        return sprintf(
            '%s{%s}',
            $selector->selector,
            implode(';', $properties),
        );
    }

    private function stringifyMedia(Media $media): string
    {
        $selectors = array_map(
            fn (Selector $selector): string => $this->stringifySelector($selector),
            $media->selectors,
        );

        return sprintf(
            '@media(%s){%s}',
            $media->rule,
            implode('', $selectors),
        );
    }
}
