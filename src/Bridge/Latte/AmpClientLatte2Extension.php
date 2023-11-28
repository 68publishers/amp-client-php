<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte;

use Latte\CompileException;
use Latte\Compiler;
use Latte\Engine;
use Latte\Helpers;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

final class AmpClientLatte2Extension extends MacroSet
{
    private function __construct(Compiler $compiler)
    {
        parent::__construct($compiler);
    }

    public static function register(Engine $latte, RendererProvider $rendererProvider, string $tagName = 'banner'): void
    {
        $latte->addProvider('ampClientRenderer', $rendererProvider);

        $latte->onCompile[] = static function (Engine $latte) use ($tagName): void {
            $macroSet = new self($latte->getCompiler());

            $macroSet->addMacro($tagName, [$macroSet, 'macroBanner']);
        };
    }

    /**
     * @throws CompileException
     */
    public function macroBanner(MacroNode $node, PhpWriter $writer): string
    {
        Helpers::removeFilter($node->modifiers, 'escape');

        return $writer
            ->using($node)
            ->write('echo ($this->global->ampClientRenderer)($this->global, %node.word, %node.args?);');
    }
}
