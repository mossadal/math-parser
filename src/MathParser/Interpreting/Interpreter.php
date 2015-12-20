<?php namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;


class Interpreter implements Visitor
{
    public function visitExpressionNode(ExpressionNode $node)
    {
        $leftValue = $node->getLeft()->accept($this);
        $operator = $node->getOperator();

        // The operator and the right side are optional, remember?
        if (!$operator)
            return $leftValue;

        $rightValue = $node->getRight()->accept($this);

        // Perform the right operation based on the operator
        switch ($operator) {
            case '+':
                return $leftValue + $rightValue;

            case '-':
                return $leftValue - $rightValue;

            case '*':
                return $leftValue * $rightValue;

            case '/':
                if ($rightValue == 0)
                    throw new Exception('Cannot divide by zero');

                return $leftValue / $rightValue;

            case '^':
                return pow($leftValue, $rightValue);

            default:
                throw new Exception('Unsupported operator: ' . $operator);
        }
    }

    public function visitNumberNode(NumberNode $node)
    {
        return intval($node->getValue());
    }

    public function visitVariableNode(VariableNode $node)
    {
        return $node->getValue();
    }

    public function visitFunctionNode(FunctionNode $node)
    {
        return $node->getValue();
    }

    public function visitConstantNode(ConstantNode $node)
    {
        switch($node->getValue()) {
            case 'pi':
                return M_PI;
            case 'e':
                return exp(1);

            default:
                throw new \Exception("Unknown constant");
        }
    }
}
