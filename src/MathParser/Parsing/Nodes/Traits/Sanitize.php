<?php
/*
* @package     Parsing
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2015 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*
*/

namespace MathParser\Parsing\Nodes\Traits;

trait Sanitize {
    /**
    * Convert ints and floats to NumberNodes
    *
    * @param Node|int|float $operand
    * @return Node
    **/
    protected function sanitize($operand)
    {
        if (is_int($operand) || is_float($operand)) return new NumberNode($operand);

        return $operand;
    }
}
