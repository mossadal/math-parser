<?php namespace MathParser\Lexing\Exceptions;

class UnexpectedTokenException extends \Exception
{
    public function __construct($expected, $actual)
    {
        $message = $this->buildMessage($expected, $actual);
        parent::__construct($message);
    }
    private function buildMessage($expected, $actual)
    {
        $expected = "Expected token to be '$expected', but got";
        $got = $actual ?: '<end of stream>';
        return "$expected '$got' instead";
    }
}
