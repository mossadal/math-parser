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
use MathParser\Parsing\Nodes\Node;

class SubtractionNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;

    /**
    * Create a Node representing 'leftOperand - rightOperand'
    *
    * Using some simplification rules, create a NumberNode or ExpressionNode
    * giving an AST correctly representing 'rightOperand - leftOperand'.
    *
    * ### Simplification rules:
    *
    * - To simplify the use of the function, convert integer params to NumberNodes
    * - If $rightOperand is null, return a unary minus node '-x' instead
    * - If $leftOperand and $rightOperand are both NumberNodes, return a single NumberNode containing their difference
    * - If $rightOperand is a NumberNode representing 0, return $leftOperand unchanged
    * - If $leftOperand and $rightOperand are equal, return '0'
    *
    * @param Node|int|float $leftOperand Minuend
    * @param Node|int|float $rightOperand Subtrahend
    * @return Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        if ($rightOperand === null) return $this->createUnaryMinusNode($leftOperand);

        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode) {
            return new NumberNode($leftOperand->getValue() + $rightOperand->getValue());
        }

        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 0) {
            return $leftOperand;
        }

        if (Node::compareNodes($leftOperand, $rightOperand)) {
            return new NumberNode(0);
        }

        return new ExpressionNode($leftOperand, '-', $rightOperand);
    }

}
