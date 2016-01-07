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

/**
 * Factory for creating an ExpressionNode representing '^'.
 *
 * Some basic simplification is applied to the resulting Node.
 *
 */
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
    * @retval Node
    */
    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        // Simplification if the exponent is a number.
        if ($rightOperand instanceof NumberNode) {
            $node = $this->numericExponent($leftOperand, $rightOperand);
            if ($node) return $node;
        }

        $node = $this->doubleExponentiation($leftOperand, $rightOperand);
        if ($node) return $node;

        return new ExpressionNode($leftOperand, '^', $rightOperand);
    }

    /** Simplify an expression x^y, when y is numeric.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     * @retval Node|null
     */
    private function numericExponent($leftOperand, $rightOperand)
    {
        // x^0 = 1
        if ($rightOperand->getValue() == 0) return new NumberNode(1);
        // x^1 = x
        if ($rightOperand->getValue() == 1) return $leftOperand;
        // Compute x^y if both are numbers.
        if ($leftOperand instanceof NumberNode) {
            return new NumberNode(pow($leftOperand->getValue(), $rightOperand->getValue()));
        }

        // No simplification found
        return null;
    }

    /** Simplify (x^a)^b when a and b are both numeric.
     * @param Node $leftOperand
     * @param Node $rightOperand
     * @retval Node|null
     */
    private function doubleExponentiation($leftOperand, $rightOperand)
    {
        // (x^a)^b -> x^(ab) for a, b numbers
        if ($leftOperand instanceof ExpressionNode && $leftOperand->getRight() instanceof NumberNode && $rightOperand instanceof NumberNode) {
            $power = new NumberNode($leftOperand->getRight()->getValue() * $rightOperand->getValue());
            $base = $leftOperand->getLeft();
            return new ExpressionNode($base, '^', $power);
        }

        // No simplification found
        return null;
    }

}
