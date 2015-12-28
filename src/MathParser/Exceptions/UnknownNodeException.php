<?php namespace MathParser\Exceptions;

class UnknownNodeException extends \Exception
{
    public function __construct($node)
    {
        parent::__construct("Unknown node: $node");
    }
}
