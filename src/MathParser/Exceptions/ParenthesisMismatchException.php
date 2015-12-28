<?php namespace MathParser\Exceptions;

class ParenthesisMismatchException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unable to match delimiters.");
    }
}
