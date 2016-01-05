<?php
/*
* @package     Parsing
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2015 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*
*/

namespace MathParser\Parsing\Nodes\Interfaces;

use MathParser\Parsing\Nodes\NumberNode;

interface ExpressionNodeFactory
{
    /**
    * Factory method to create an ExpressionNode with given operands.
    *
    * @param mixed $leftOperand
    * @param mixed $rightOperand
    * @return ExpressionNode|NumberNode
    */
    public function makeNode($leftOperand, $rightOperand);
}
