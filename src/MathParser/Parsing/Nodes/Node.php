<?php namespace MathParser\Parsing\Nodes;

use MathParser\Interpreting\Visitors\Visitable;
use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;
use MathParser\Lexing\TokenPrecedence;

abstract class Node implements Visitable
{
    public static function factory(Token $token)
    {
        switch($token->getType()) {
            case TokenType::PosInt:
                $node = new NumberNode($token->getValue());
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
                return $node1->getValue() == $node2->getValue();
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


            throw new \Exception("Unknown node type");
    }

}
