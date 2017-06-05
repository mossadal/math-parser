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

use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\IntegerNode;
use MathParser\Parsing\Nodes\RationalNode;

use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\Traits\Sanitize;
use MathParser\Parsing\Nodes\Traits\Numeric;

/**
* Factory for creating an ExpressionNode representing '='.
*
* Some basic simplification is applied to the resulting Node.
*
*/
class GreaterNodeFactory implements ExpressionNodeFactory
{
    use Sanitize;
    use Numeric;

    public function makeNode($leftOperand, $rightOperand)
    {
        $leftOperand = $this->sanitize($leftOperand);
        $rightOperand = $this->sanitize($rightOperand);

        $node = $this->numericTerms($leftOperand, $rightOperand);
        if ($node) return $node;

        return new ExpressionNode($leftOperand, '>', $rightOperand);
    }

    protected function numericTerms($leftOperand, $rightOperand)
    {
        if (!$this->isNumeric($leftOperand) || !$this->isNumeric($rightOperand)) {
            return null;
        }
        $type = $this->resultingType($leftOperand, $rightOperand);

        switch($type) {
            case Node::NumericFloat:
                $result = ($leftOperand->getValue() > $rightOperand->getValue());
                return new NumberNode($result);

            case Node::NumericRational:
                $leftValue = ($leftOperand->getNumerator() / $leftOperand->getDenominator());
                $rightValue =  ($rightOperand->getDenominator() / $rightOperand->getNumerator());
                $result = ($leftValue > $rightValue);
                return new IntegerNode($result);

            case Node::NumericInteger:
                $result = ($leftOperand->getValue() > $rightOperand->getValue());
                return new IntegerNode($result);
        }


        return null;
    }
}
