<?php
/*
 * @package     Exceptions
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

namespace MathParser\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an
 * unknown node type.
 *
 * This should not happen under normal circumstances.
 */
class UnknownNodeException extends MathParserException
{
    public function __construct($node)
    {
        parent::__construct("Unknown node: $node");
    }
}
