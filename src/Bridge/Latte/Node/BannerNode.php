<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\Node;

use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * {banner $positionCode [, $resources]}
 */
final class BannerNode extends StatementNode
{
    public ExpressionNode $positionCode;

    public ?ArrayNode $options = null;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = new self;
        $node->positionCode = $tag->parser->parseUnquotedStringOrExpression();

        if ($tag->parser->stream->tryConsume(',')) {
            $node->options = $tag->parser->parseArguments();
        }

        return $node;
    }

    public function print(PrintContext $context): string
    {
        if (null !== $this->options) {
            return $context->format(
                'echo ($this->global->ampClientRenderer)($this->global, %node, %node?);',
                $this->positionCode,
                $this->options,
            );
        }

        return $context->format(
            'echo ($this->global->ampClientRenderer)($this->global, %node);',
            $this->positionCode,
        );
    }

    public function &getIterator(): Generator
    {
        yield $this->positionCode;

        if (null !== $this->options) {
            yield $this->options;
        }
    }
}
