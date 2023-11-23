<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use function array_merge;

final class Templates
{
    public const TemplateSingle = 'single';
    public const TemplateRandom = 'random';
    public const TemplateMultiple = 'multiple';
    public const TemplateNotFound = 'notFound';

    /**
     * @var array{
     *      single?: string,
     *      random?: string,
     *      multiple?: string,
     *      'notFound'?: string,
     *  }
     */
    private array $filesMap;

    /**
     * @param array{
     *      single?: string,
     *      random?: string,
     *      multiple?: string,
     *      notFound?: string,
     *  } $filesMap
     */
    public function __construct(array $filesMap)
    {
        $this->filesMap = $filesMap;
    }

    /**
     * @throws RendererException
     */
    public function getTemplateFile(string $type): string
    {
        $filename = $this->filesMap[$type] ?? null;

        if (null === $filename) {
            throw RendererException::templateFileNotDefined($type);
        }

        if (!file_exists($filename)) {
            throw RendererException::templateFileNotFound($filename);
        }

        return $filename;
    }

    public function override(self $templates): self
    {
        return new self(
            array_merge(
                $this->filesMap,
                $templates->filesMap,
            ),
        );
    }
}
