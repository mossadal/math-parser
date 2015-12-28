<?php namespace MathParser\Exceptions;

class UnknownConstantException extends \Exception
{
    public function __construct($operator)
    {
        parent::__construct("Unknown constant $operator.");
    }
}
