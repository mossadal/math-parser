<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

namespace MathParser\Parsing\Nodes;

use MathParser\Interpreting\Visitors\Visitable;
use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;
use MathParser\Lexing\TokenPrecedence;


use MathParser\Exceptions\UnknownNodeException;

abstract class Node implements Visitable
{
    public static function factory(Token $token)
    {
        switch($token->getType()) {
            case TokenType::PosInt:
                $x = intval(str_replace("~", "-", $token->getValue()));
                $node = new NumberNode($x);
                break;
            case TokenType::Identifier:
                $node = new VariableNode($token->getValue());
                break;
            case TokenType::Constant:
                $node = new ConstantNode($token->getValue());
                break;
            default:
                $node = null;
                break;
        }

        return $node;
    }

    public static function compareNodes($node1, $node2)
    {
            if ($node1 === null && $node2 === null) return true;
            if ($node1 === null || $node2 === null) return false;

            if ($node1 instanceof ConstantNode) {
                if (!($node2 instanceof ConstantNode)) return false;
                return $node1->getName() == $node2->getName();
            }
            if ($node1 instanceof ExpressionNode) {
                if (!($node2 instanceof ExpressionNode)) return false;
                return self::compareNodes($node1->getRight(), $node2->getRight()) && self::compareNodes($node1->getLeft(), $node2->getLeft());
            }
            if ($node1 instanceof FunctionNode) {
                if (!($node2 instanceof FunctionNode)) return false;
                return self::compareNodes($node1->getOperand(), $node2->getOperand());
            }
            if ($node1 instanceof NumberNode) {
                if (!($node2 instanceof NumberNode)) return false;
                return $node1->getValue() == $node2->getValue();
            }
            if ($node1 instanceof VariableNode) {
                if (!($node2 instanceof VariableNode)) return false;
                return $node1->getName() == $node2->getName();
            }


            throw new UnknownNodeException($node1);
    }

    /**
     * Convenience function for evaluating a tree, using
     * the Evaluator class.
     *
     * Example usage:
     * $parser = new StdMathParser();
     * $node = $parser->parse('sin(x)cos(y)');
     * $functionValue = $node->evaluate( array( 'x' => 1.3, 'y' => 1.4 ) );
     *
     * @param array $variables key-value array of variable values
     * @return floatval
     **/
    public function evaluate($variables)
    {
        $evaluator = new Evaluator($variables);
        return $this->accept($evaluator);
    }


    public function complexity()
    {
        if ($this instanceof NumberNode || $this instanceof VariableNode || $this instanceof ConstantNode) {
            return 1;
        } elseif ($this instanceof FunctionNode) {
            return 5+$this->getOperand()->complexity();
        } elseif ($this instanceof ExpressionNode) {
            $operator = $this->getOperator();
            $left = $this->getLeft();
            $right = $this->getRight();
            switch ($operator) {
                case '+':
                case '-':
                    return 2 + $left->complexity() + (($right === null) ? 0 : $right->complexity());

                case '*':
                case '/':
                    return 2 + $left->complexity() + (($right === null) ? 0 : $right->complexity());

                case '^':
                    return 8 + $left->complexity() + (($right === null) ? 0 : $right->complexity());

                default:
                    return -1;
            }
        } else {
            return 1000;
        }
    }

}
