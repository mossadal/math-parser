<?php namespace MathParser\Lexing;

use MathParser\Lexing\TokenType;

class Token
{
    private $value;
    private $type;
    private $precedence;
    private $associativity;

    public function __construct($value, $type, $precedence, $associativity = TokenAssociativity::Right)
    {
        $this->value = $value;
        $this->type = $type;
        $this->precedence = $precedence;
        $this->associativity = $associativity;

    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getPrecedence()
    {
        return $this->precedence;
    }

    public function getAssociativity()
    {
        return $this->associativity;
    }

    public function getArity()
    {
        switch($this->type) {
            case TokenType::UnaryMinus:
                return 1;
            case TokenType::PosInt;
            case TokenType::Constant:
            case TokenType::Identifier:
                return 0;
            default:
                return 2;
        }
    }

    public function __toString()
    {
        return "Token: [$this->value, $this->type]";
    }
}
