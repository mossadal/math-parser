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

class DivisionNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;


    /**
    * Create a Node representing '$leftOperand/$rightOperand'
    *
    * Using some simplification rules, create a NumberNode or ExpressionNode
    * giving an AST correctly representing '$leftOperand/$rightOperand'.
    *
    * ### Simplification rules:
    *
    * - To simplify the use of the function, convert integer params to NumberNodes
    * - If $leftOperand is a NumberNode representing 0, return 0
    * - If $rightOperand is a NumberNode representing 1, return $leftOperand
    * - If $leftOperand and $rightOperand are equal, return '1'
    *
    * @param Node|int $leftOperand Numerator
    * @param Node|int $rightOperand Denominator
    * @return Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        // Return rational number?
        // if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode)
        //    return new NumberNode($leftOperand->getValue() / $rightOperand->getValue());

        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 0) {
            throw new DivisionByZeroException();
        }

        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 1) {
            return $leftOperand;
        }
        if ($leftOperand instanceof NumberNode && $leftOperand->getValue() == 0) {
            return new NumberNode(0);
        }

        if (Node::compareNodes($leftOperand,$rightOperand)) {
            return new NumberNode(1);
        }

        return new ExpressionNode($leftOperand, '/', $rightOperand);
    }

}
