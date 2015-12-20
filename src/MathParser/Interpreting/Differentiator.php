<?php namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;


class Differentiator implements Visitor
{
    private $variable;

    public function __construct($variable)
    {
        $this->variable = $variable;
    }

    public function visitExpressionNode(ExpressionNode $node)
    {
        $operator = $node->getOperator();

        $leftValue = $node->getLeft()->accept($this);

        if ($node->getRight()) {
            $rightValue = $node->getRight()->accept($this);
        } else {
            $rightValue = null;
        }

        // Perform the right operation based on the operator
        switch ($operator) {
            case '+':
                if ($leftValue instanceof NumberNode && $leftValue->getValue() == 0) return $rightValue;
                if ($rightValue instanceof NumberNode && $rightValue->getValue() == 0) return $leftValue;
                if ($leftValue instanceof NumberNode && $rightValue instanceof NumberNode)
                    return new NumberNode($leftValue->getValue() + $rightValue->getValue());

                return new ExpressionNode($leftValue, '+', $rightValue);

            case '-':
                if ($leftValue instanceof NumberNode && $leftValue->getValue() == 0) return $rightValue;
                if ($rightValue instanceof NumberNode && $rightValue->getValue() == 0) return $leftValue;
                if ($leftValue instanceof NumberNode && $rightValue instanceof NumberNode)
                    return new NumberNode($leftValue->getValue() - $rightValue->getValue());

                return new ExpressionNode($leftValue, '-', $rightValue);

            // Product rule
            case '*':
                if ($leftValue instanceof NumberNode && $leftValue->getValue() == 1) $term1 = $node->getRight();
                elseif ($leftValue instanceof NumberNode && $leftValue->getValue() == 0) $term1 = null;
                else $term1 = new ExpressionNode($leftValue, '*', $node->getRight());

                if ($rightValue instanceof NumberNode && $rightValue->getValue() == 1) $term2 = $node->getLeft();
                elseif ($rightValue instanceof NumberNode && $rightValue->getValue() == 0) $term2 = null;
                else $term2 = new ExpressionNode($node->getLeft(), '*', $rightValue);

                if ($term1 === null && $term2 === null) return new NumberNode(0);
                if ($term1 === null) return $term2;
                if ($term2 === null) return $term1;

                return new ExpressionNode($term1, '+', $term2);

            // Quotient rule (f/g)' = (f'g - fg')/g^2
            case '/':
                $term1 = new ExpressionNode($leftValue, '*', $node->getRight());
                $term2 = new ExpressionNode($node->getLeft(), '*', $rightValue);
                $numerator = new ExpressionNode($term1, '-', $term2);
                $denominator = new ExpressionNode($node->getRight(), '^', new NumberNode(2));
                return new ExpressionNode($numerator, '/', $denominator);

            // f^g = exp(g log(f)), so (f^g)' = f^g (g'log(f) + g/f)
            case '^':
                $base = $node->getLeft();
                $exponent = $node->getRight();

                if ($exponent instanceof NumberNode) {
                    $power = $exponent->getValue();

                    switch($power) {
                        case 1:
                            return $inner;
                        case 2:
                            return new ExpressionNode(new NumberNode($power), '*', new ExpressionNode($leftValue, '*', $base));
                        default:
                            // (f^n)' = n f^(n-1) f'
                            $fpow = new ExpressionNode($base, '^', new NumberNode($power-1));

                            return new ExpressionNode(new NumberNode($power), '*', new ExpressionNode($fpow, '*', $leftValue));
                    }

                } else {
                    $term1 = new ExpressionNode($rightValue, '*', new FunctionNode('log', $node->getLeft()));
                    $term2 = new ExpressionNode($node->getRight(), '/', $node->getLeft());
                    $factor2 = new ExpressionNode($term1, '+', $term2);

                    return new ExpressionNode($node, '*', $factor2);
                }

            default:
                throw new Exception('Unsupported operator: ' . $operator);
        }
    }

    public function visitNumberNode(NumberNode $node)
    {
        return new NumberNode(0);
    }

    public function visitVariableNode(VariableNode $node)
    {
        if ($node->getName() == $this->variable) return new NumberNode(1);
        else return new NumberNode(0);
    }

    public function visitFunctionNode(FunctionNode $node)
    {
        $inner = $node->getOperand()->accept($this);

        switch ($node->getName()) {

            case 'sin':
                $df = new FunctionNode('cos', $node->getOperand());
                break;
            case 'cos':
                $sin = new FunctionNode('sin', $node->getOperand());
                $df = new ExpressionNode($sin, '-');
                break;
            case 'tan':
                $tansquare = new ExpressionNode($node, '^', new NumberNode(2));
                $df = new ExpressionNode(new NumberNode(1), '+', $tansquare);
                break;
            case 'exp':
                $df = new FunctionNode('exp', $node->getOperand());
                break;
            case 'log':
                return new ExpressionNode($inner, '/', $node->getOperand());

        }

        // Simplification: 1 * df = df
        if ($inner instanceof NumberNode && $inner->getValue() == 1) return $df;

        // Simplification: 0 * df = 0
        if ($inner instanceof NumberNode && $inner->getValue() == 0) return new NumberNode(0);

        return new ExpressionNode($inner, '*', $df);
    }

    public function visitConstantNode(ConstantNode $node)
    {
        return new NumberNode(0);
    }
}
