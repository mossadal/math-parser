<?php
/*
* @package     Parsing
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2015 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*
*/

namespace MathParser\Parsing\Nodes\Factories;

use MathParser\Parsing\Nodes\Interfaces\ExpressionNodeFactory;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\Traits\Sanitize;

class AdditionNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;

    /**
    * Create a Node representing 'leftOperand + rightOperand'
    *
    * Using some simplification rules, create a NumberNode or ExpressionNode
    * giving an AST correctly representing 'leftOperand + rightOperand'.
    *
    * ### Simplification rules:
    *
    * - To simplify the use of the function, convert integer or float params to NumberNodes
    * - If $leftOperand and $rightOperand are both NumberNodes, return a single NumberNode containing their sum
    * - If $leftOperand or $rightOperand are NumberNodes representing 0, return the other term unchanged
    *
    * @param Node|int $leftOperand First term
    * @param Node|int $rightOperand Second term
    * @return Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode) {
            return new NumberNode($leftOperand->getValue() + $rightOperand->getValue());
        }

        if ($leftOperand instanceof NumberNode && $leftOperand->getValue() == 0) {
            return $rightOperand;
        }
        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 0) {
            return $leftOperand;
        }

        return new ExpressionNode($leftOperand, '+', $rightOperand);
    }
}
