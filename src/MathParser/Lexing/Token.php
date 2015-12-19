<?php namespace MathParser\Lexing;

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

    public function getPrecedence()
    {
        return $this->precedence;
    }

    public function getAssociativity()
    {

    }
}
