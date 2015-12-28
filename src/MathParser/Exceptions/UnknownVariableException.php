<?php namespace MathParser\Exceptions;

class UnknownVariableException extends \Exception
{
    public function __construct($variable)
    {
        parent::__construct("Unknown variable $variable.");
    }
}
