<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Phtml;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\OutputBuffer;
use SixtyEightPublishers\AmpClient\Renderer\RendererBridgeInterface;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use Throwable;
use function array_merge;
use function file_exists;

final class PhtmlRendererBridge implements RendererBridgeInterface
{
    public const TemplateSingle = 'single';
    public const TemplateRandom = 'random';
    public const TemplateMultiple = 'multiple';
    public const TemplateNotFound = 'not-found';

    /**
     * @var array{
     *      single: string,
     *      random: string,
     *      multiple: string,
     *      'not-found': string,
     *  }
     */
    private array $templates;

    /**
     * @param array{
     *      single?: string,
     *      random?: string,
     *      multiple?: string,
     *      not-found?: string,
     *  } $templatesOverrides
     */
    public function __construct(array $templatesOverrides = [])
    {
        $this->templates = array_merge(
            [
                self::TemplateSingle => __DIR__ . '/Templates/single.phtml',
                self::TemplateRandom => __DIR__ . '/Templates/random.phtml',
                self::TemplateMultiple => __DIR__ . '/Templates/multiple.phtml',
                self::TemplateNotFound => __DIR__ . '/Templates/not-found.phtml',
            ],
            $templatesOverrides,
        );
    }

    /**
     * @throws Throwable
     */
    public function renderNotFound(Position $position): string
    {
        $filename = $this->getTemplateFilename(self::TemplateNotFound);

        return OutputBuffer::capture(function () use ($filename, $position) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderSingle(Position $position, ?Banner $banner): string
    {
        $filename = $this->getTemplateFilename(self::TemplateSingle);

        return OutputBuffer::capture(function () use ($filename, $position, $banner) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderRandom(Position $position, ?Banner $banner): string
    {
        $filename = $this->getTemplateFilename(self::TemplateRandom);

        return OutputBuffer::capture(function () use ($filename, $position, $banner) {
            require $filename;
        });
    }

    /**
     * @throws Throwable
     */
    public function renderMultiple(Position $position, array $banners): string
    {
        $filename = $this->getTemplateFilename(self::TemplateMultiple);

        return OutputBuffer::capture(function () use ($filename, $position, $banners) {
            require $filename;
        });
    }

    /**
     * @throws RendererException
     */
    private function getTemplateFilename(string $type): string
    {
        $filename = $this->templates[$type] ?? '';

        if (!file_exists($filename)) {
            throw RendererException::templateFileNotFound($filename);
        }

        return $filename;
    }
}
