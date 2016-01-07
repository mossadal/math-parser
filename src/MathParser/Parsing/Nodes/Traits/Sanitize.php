<?php
/*
* @package     Parsing
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2015 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*
*/

/** @namespace MathParser::Parsing::Nodes::Traits
 *
 * Traits for Nodes
 */
namespace MathParser\Parsing\Nodes\Traits;

use MathParser\Parsing\Nodes\NumberNode;

/**
 * Trait for upgrading numbers (ints and floats) to NumberNode,
 * making it possible to call the Node constructors directly
 * with numbers, making the code cleaner.
 *
 */
trait Sanitize {
    /**
    * Convert ints and floats to NumberNodes
    *
    * @param Node|int|float $operand
    * @retval Node
    **/
    protected function sanitize($operand)
    {
        if (is_int($operand) || is_float($operand)) return new NumberNode($operand);

        return $operand;
    }
}
