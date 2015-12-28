<?php namespace MathParser\Exceptions;

class SyntaxErrorException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Syntax error.");
    }
}
