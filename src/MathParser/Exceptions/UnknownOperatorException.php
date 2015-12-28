<?php namespace MathParser\Exceptions;

class UnknownOperatorException extends \Exception
{
    public function __construct($operator)
    {
        parent::__construct("Unknown operator $operator.");
    }
}
