<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\Templates;
use Tester\Assert;
use Tester\TestCase;
use function realpath;

require __DIR__ . '/../bootstrap.php';

final class TemplatesTest extends TestCase
{
    public function testExceptionShouldBeThrownWhenTemplateFileNotFound(): void
    {
        $templates = new Templates([
            Templates::TemplateSingle => __DIR__ . '/path/to/missing-file.phtml',
        ]);

        Assert::exception(
            static fn () => $templates->getTemplateFile(Templates::TemplateSingle),
            RendererException::class,
            'Template file "%A%/path/to/missing-file.phtml" not found.',
        );
    }

    public function testExceptionShouldBeThrownWhenTemplateFileNotDefined(): void
    {
        $templates = new Templates([
            Templates::TemplateSingle => __DIR__ . '/path/to/missing-file.phtml',
        ]);

        Assert::exception(
            static fn () => $templates->getTemplateFile(Templates::TemplateMultiple),
            RendererException::class,
            'Template file of type "multiple" not defined.',
        );
    }

    public function testTemplateFileShouldBeReturned(): void
    {
        $filename = realpath(__DIR__ . '/../resources/renderer/not-found/templates/not-found1.phtml');
        $templates = new Templates([
            Templates::TemplateNotFound => $filename,
        ]);

        Assert::same($filename, $templates->getTemplateFile(Templates::TemplateNotFound));
    }

    public function testTemplatesShouldBeOverridden(): void
    {
        $notFound = realpath(__DIR__ . '/../resources/renderer/not-found/templates/not-found1.phtml');
        $single = $notFound; # can be same for testing purposes

        $notFoundOverridden = realpath(__DIR__ . '/../resources/renderer/not-found/templates/not-found2.phtml');

        $templates = new Templates([
            Templates::TemplateNotFound => $notFound,
            Templates::TemplateSingle => $single,
        ]);

        $overriddenTemplates = $templates->override(new Templates([
            Templates::TemplateNotFound => $notFoundOverridden,
        ]));

        Assert::notSame($templates, $overriddenTemplates);

        Assert::same($notFound, $templates->getTemplateFile(Templates::TemplateNotFound));
        Assert::same($single, $templates->getTemplateFile(Templates::TemplateSingle));

        Assert::same($notFoundOverridden, $overriddenTemplates->getTemplateFile(Templates::TemplateNotFound));
        Assert::same($single, $overriddenTemplates->getTemplateFile(Templates::TemplateSingle));
    }
}

(new TemplatesTest())->run();
