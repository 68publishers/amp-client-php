<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

interface ContentInterface
{
    public const TypeImage = 'img';
    public const TypeHtml = 'html';

    public function getBreakpoint(): ?int;
}
