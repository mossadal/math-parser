<?php namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;

use MathParser\Exceptions\UnknownVariableException;
use MathParser\Exceptions\UnknownConstantException;
use MathParser\Exceptions\UnknownFunctionException;
use MathParser\Exceptions\UnknownOperatorException;
use MathParser\Exceptions\DivisionByZeroException;

class Evaluator implements Visitor
{
    private $variables;

    public function __construct($variables=null)
    {
        $this->variables = $variables;
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
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
                return $leftValue + $rightValue;
            case '-':
                return $rightValue === null ? -$leftValue : $leftValue - $rightValue;
            case '*':
                return $rightValue * $leftValue;
            case '/':
                if ($rightValue == 0) throw new DivisionByZeroException();
                return $leftValue/$rightValue;
            case '^':
                return pow($leftValue, $rightValue);

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    public function visitNumberNode(NumberNode $node)
    {
        return $node->getValue();
    }

    public function visitVariableNode(VariableNode $node)
    {
        $x = $node->getName();

        if (array_key_exists($x, $this->variables))
            return $this->variables[$x];

        else
            throw new UnknownVariableException($x);
    }

    public function visitFunctionNode(FunctionNode $node)
    {
        $inner = $node->getOperand()->accept($this);

        switch ($node->getName()) {

            case 'sin':
                return sin($inner);
            case 'cos':
                return cos($inner);
            case 'tan':
                return tan($inner);
            case 'cot':
                return 1/tan($inner);

            case 'arcsin':
                return asin($inner);
            case 'arccos':
                return acos($inner);
            case 'arctan':
                return atan($inner);
            case 'arccot':
                return pi()/2-atan($inner);

            case 'exp':
                return exp($inner);
            case 'log':
                return log($inner);
            case 'lg':
                return log10($inner);

            case 'sqrt':
                return sqrt($inner);
            default:
                throw new UnknownFunctionException($node->getName());

        }
    }

    public function visitConstantNode(ConstantNode $node)
    {
        switch($node->getName()) {
            case 'pi':
                return M_PI;
            case 'e':
                return exp(1);
            default:
                throw new UnknownConstantException($node->getName());;
        }

    }
}
