<?php namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;

use MathParser\Lexing\StdMathLexer;
use MathParser\Lexing\TokenAssociativity;

use MathParser\Exceptions\UnknownConstantException;

class LaTeXPrinter implements Visitor
{
    private $lexer;

    public function __construct()
    {
        $this->lexer = new StdMathLexer();
    }

    public function visitExpressionNode(ExpressionNode $node)
    {
        $left = $node->getLeft();
        $leftValue = $this->parenthesize($left, $node->getOperator());
        $operator = $node->getOperator();

        $right = $node->getRight();

        if ($operator == '*') {
            $operator = '';
            if ($left instanceof FunctionNode || $right instanceof NumberNode || ($right instanceof ExpressionNode && $right->getLeft() instanceof NumberNode)) {
                $operator = '\cdot ';
            }
        }

        if ($right) {
            $rightValue = $this->parenthesize($right, $node->getOperator());

            switch($operator) {
                case '/':
                    // No parantheses needed
                    return '\frac{'.$left->accept($this).'}{'.$right->accept($this).'}';
                case '^':
                    return $leftValue.'^'.$this->bracesNeeded($right);
                default:
                    return "$leftValue$operator$rightValue";
            }

        } else {
            return "$operator$leftValue";
        }

    }

    public function visitNumberNode(NumberNode $node)
    {
        $val = $node->getValue();
        return "$val";
    }

    public function visitVariableNode(VariableNode $node)
    {
        return $node->getName();
    }

    public function visitFunctionNode(FunctionNode $node)
    {
        $functionName = $node->getName();
        $operand = $this->parenthesize($node->getOperand(), '*', true);

        switch($functionName) {
            case 'sqrt': return "\\$functionName{".$node->getOperand()->accept($this).'}';
            case 'exp':
                $operand = $node->getOperand();

                if ($operand->complexity() < 6) {
                    return 'e^'.$this->bracesNeeded($operand);
                } else {
                    return '\exp('.$operand->accept($this).')';
                }
        }

        return "\\$functionName$operand";
    }

    public function visitConstantNode(ConstantNode $node)
    {
        switch($node->getName()) {
            case 'pi': return '\pi';
            case 'e': return 'e';
            default: throw new UnknownConstantException($node->getName());
        }
    }

    public function parenthesize(Node $node, $cutoff='*', $addSpace=false)
    {
        $cutoffToken = $this->lexer->tokenize($cutoff);

        $text = $node->accept($this);

        if ($node instanceof ExpressionNode) {
            $thisToken = $this->lexer->tokenize($node->getOperator());

            if ($thisToken[0]->getPrecedence() < $cutoffToken[0]->getPrecedence() ||
                ($thisToken[0]->getPrecedence() == $cutoffToken[0]->getPrecedence() && $thisToken[0]->getAssociativity() == TokenAssociativity::Left)
               ) {
                return "($text)";
            }
        }

        if ($addSpace) return " $text";
        else return $text;
    }

    public function bracesNeeded(Node $node)
    {
        if ($node instanceof VariableNode || $node instanceof ConstantNode) {
            return $node->accept($this);
        } elseif ($node instanceof NumberNode && $node->getValue() >= 0 && $node->getValue() <= 9) {
            return $node->accept($this);
        } else {
            return '{'.$node->accept($this).'}';
        }
    }
}
