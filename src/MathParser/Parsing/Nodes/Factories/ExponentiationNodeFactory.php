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

class ExponentiationNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;

    /**
    * Create a Node representing '$leftOperand^$rightOperand'
    *
    * Using some simplification rules, create a NumberNode or ExpressionNode
    * giving an AST correctly representing '$leftOperand^$rightOperand'.
    *
    * ### Simplification rules:
    *
    * - To simplify the use of the function, convert integer params to NumberNodes
    * - If $leftOperand and $rightOperand are both NumberNodes, return a single NumberNode containing x^y
    * - If $rightOperand is a NumberNode representing 0, return '1'
    * - If $rightOperand is a NumberNode representing 1, return $leftOperand
    * - If $leftOperand is already a power x=a^b and $rightOperand is a NumberNode, return a^(b*y)
    *
    * @param Node|int $leftOperand Minuend
    * @param Node|int $rightOperand Subtrahend
    * @return Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 0) {
            return new NumberNode(1);
        }
        if ($rightOperand instanceof NumberNode && $rightOperand->getValue() == 1) {
            return $leftOperand;
        }

        if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode) {
            return new NumberNode(pow($leftOperand->getValue(), $rightOperand->getValue()));
        }

        // (x^a)^b -> x^(ab) for a, b numbers
        if ($leftOperand instanceof ExpressionNode && $leftOperand->getRight() instanceof NumberNode && $rightOperand instanceof NumberNode) {
            $power = new NumberNode($leftOperand->getRight()->getValue() * $rightOperand->getValue());
            $base = $leftOperand->getLeft();
            return new ExpressionNode($base, '^', $power);
        }

        return new ExpressionNode($leftOperand, '^', $rightOperand);
    }

}
