<?php namespace MathParser\Exceptions;

class UnknownFunctionException extends \Exception
{
    public function __construct($operator)
    {
        parent::__construct("Unknown function $operator.");
    }
}
