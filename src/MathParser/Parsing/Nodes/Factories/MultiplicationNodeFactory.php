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

class MultiplicationNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;

    /**
    * Create a Node representing 'leftOperand * rightOperand'
    *
    * Using some simplification rules, create a NumberNode or ExpressionNode
    * giving an AST correctly representing 'leftOperand * rightOperand'.
    *
    * ### Simplification rules:
    *
    * - To simplify the use of the function, convert integer params to NumberNodes
    * - If $leftOperand and $rightOperand are both NumberNodes, return a single NumberNode containing their product
    * - If $leftOperand or $rightOperand is a NumberNode representing 0, return '0'
    * - If $leftOperand or $rightOperand is a NumberNode representing 1, return the other factor
    *
    * @param Node|int $leftOperand First factor
    * @param Node|int $rightOperand Second factor
    * @return Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode) {
            return new NumberNode($leftOperand->getValue() * $rightOperand->getValue());
        }

        if ($leftOperand instanceof NumberNode && $leftOperand->getValue() == 1) {
            return $rightOperand;
        }
        if ($leftOperand instanceof NumberNode && $leftOperand->getValue() == 0) {
            return new NumberNode(0);
        }

        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 1) {
            return $leftOperand;
        }
        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 0) {
            return new NumberNode(0);
        }

        return new ExpressionNode($leftOperand, '*', $rightOperand);
    }

}
