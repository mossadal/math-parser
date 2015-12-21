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

    private function createAdditionNode($x, $y)
    {
        if ($x instanceof NumberNode && $y instanceof NumberNode)
            return new NumberNode($x->getValue() + $y->getValue());

        if ($x instanceof NumberNode && $x->getValue() == 0) return $y;
        if ($y instanceof NumberNode && $y->getValue() == 0) return $x;

        return new ExpressionNode($x, '+', $y);
    }

    // Perhaps we should return a unary minus node for "0-y"?
    private function createSubtractionNode($x, $y)
    {
        if ($x instanceof NumberNode && $y instanceof NumberNode)
            return new NumberNode($x->getValue() + $y->getValue());

        if ($y instanceof NumberNode && $y->getValue() == 0) return $x;

        return new ExpressionNode($x, '-', $y);
    }

    private function createUnaryMinusNode($x)
    {
        if ($x instanceof NumberNode) return new NumberNode(-$x->getValue());

        return new ExpressionNode($x, '-', null);
    }

    private function createMultiplicationNode($x, $y)
    {
        if ($x instanceof NumberNode && $y instanceof NumberNode)
            return new NumberNode($x->getValue() * $y->getValue());

        if ($x instanceof NumberNode && $x->getValue() == 1) return $y;
        if ($x instanceof NumberNode && $x->getValue() == 0) return new NumberNode(0);

        if ($y instanceof NumberNode && $y->getValue() == 1) return $x;
        if ($y instanceof NumberNode && $y->getValue() == 0) return new NumberNode(0);

        return new ExpressionNode($x, '*', $y);
    }

    private function createDivisionNode($x, $y)
    {
        // Return rational number?
        // if ($x instanceof NumberNode && $y instanceof NumberNode)
        //    return new NumberNode($x->getValue() / $y->getValue());

        if ($y instanceof NumberNode && $y->getValue() == 0)
            throw new \Exception("Division by zero error.");

        if ($y instanceof NumberNode && $y->getValue() == 1) return $x;

        if ($x instanceof NumberNode && $x->getValue() == 0) return new NumberNode(0);

        return new ExpressionNode($x, '/', $y);
    }

    private function createExponentiationNode($x, $y)
    {
        if ($y instanceof NumberNode && $y->getValue() == 0) return new NumberNode(1);
        if ($y instanceof NumberNode && $y->getValue() == 1) return $x;

        return new ExpressionNode($x, '^', $y);
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
                return $this->createAdditionNode($leftValue, $rightValue);
            case '-':
                return $this->createSubtractionNode($leftValue, $rightValue);

            // Product rule (fg)' = fg' + f'g
            case '*':
                return $this->createAdditionNode(
                    $this->createMultiplicationNode($node->getLeft(), $rightValue),
                    $this->createMultiplicationNode($leftValue, $node->getRight())
                );

            // Quotient rule (f/g)' = (f'g - fg')/g^2
            case '/':
                $term1 = $this->createMultiplicationNode($leftValue, $node->getRight());
                $term2 = $this->createMultiplicationNode($node->getLeft(), $rightValue);
                $numerator = $this->createSubtractionNode($term1, $term2);
                $denominator = $this->createExponentiationNode($node->getRight(), new NumberNode(2));
                return $this->createDivisionNode($numerator, $denominator);

            // f^g = exp(g log(f)), so (f^g)' = f^g (g'log(f) + g/f)
            case '^':
                $base = $node->getLeft();
                $exponent = $node->getRight();

                if ($exponent instanceof NumberNode) {
                    $power = $exponent->getValue();

                    switch($power) {
                        case 1:
                            return $leftValue;
                        case 2:
                            return $this->createMultiplicationNode(new NumberNode($power), $this->createMultiplicationNode($leftValue, $base));
                        default:
                            // (f^n)' = n f^(n-1) f'
                            $fpow = new ExpressionNode($base, '^', new NumberNode($power-1));

                            return $this->createMultiplicationNode(new NumberNode($power), $this->createMultiplicationNode($fpow, $leftValue));
                    }

                } else {
                    $term1 = $this->createMultiplicationNode($rightValue, new FunctionNode('log', $node->getLeft()));
                    $term2 = new ExpressionNode($node->getRight(), '/', $node->getLeft());
                    $factor2 = $this->createAdditionNode($term1, $term2);

                    return $this->createMultiplicationNode($node, $factor2);
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
            case 'cot':
                $cotsquare = New ExpressionNode($node, '^', new NumberNode(2));
                $df = $this->createAdditionNode(new ExpressionNode(new NumberNode(1), '-'), $cotsquare);
                break;

            case 'arcsin':
                $denom = new FunctionNode('sqrt',
                    $this->createSubtractionNode(new NumberNode(1), $this->createExponentiationNode($node->getOperand(), new NumberNode(2))));
                $df = $this->createDivisionNode(new NumberNode(1), $denom);
                break;

            case 'arccos':
                $denom = new FunctionNode('sqrt',
                    $this->createSubtractionNode(new NumberNode(1), $this->createExponentiationNode($node->getOperand(), new NumberNode(2))));
                $df = $this->createDivisionNode(new ExpressionNode(new NumberNode(1), '-'), $denom);
                break;

            case 'arctan':
                $denom = $this->createAdditionNode(new NumberNode(1), $this->createExponentiationNode($node->getOperand(),  new NumberNode(2)));
                $df = $this->createDivisionNode(new NumberNode(1), $denom);
                break;

            case 'arccot':
                $denom = $this->createAdditionNode(new NumberNode(1), $this->createExponentiationNode($node->getOperand(),  new NumberNode(2)));
                $df = new ExpressionNode($this->createDivisionNode(new NumberNode(1), $denom), '-');
                break;

            case 'exp':
                $df = new FunctionNode('exp', $node->getOperand());
                break;
            case 'log':
                return new ExpressionNode($inner, '/', $node->getOperand());

            case 'sqrt':
                $denom = $this->createMultiplicationNode(new NumberNode(2), $node);
                $df = $this->createDivisionNode(new NumberNode(1), $denom);
                break;

            default:
                throw new \Exception("Differentiator encountered unknown function: ". $node->getName());

        }

        return $this->createMultiplicationNode($inner, $df);
    }

    public function visitConstantNode(ConstantNode $node)
    {
        return new NumberNode(0);
    }
}
