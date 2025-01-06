<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace eLightUp\Twig\TokenParser;

use eLightUp\Twig\Error\SyntaxError;
use eLightUp\Twig\Node\Node;
use eLightUp\Twig\Parser;
use eLightUp\Twig\Token;

/**
 * Interface implemented by token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface TokenParserInterface
{
    /**
     * Sets the parser associated with this token parser.
     */
    public function setParser(Parser $parser): void;

    /**
     * Parses a token and returns a node.
     *
     * @return Node
     *
     * @throws SyntaxError
     */
    public function parse(Token $token);

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string
     */
    public function getTag();
}
