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
}
