<?php

use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;

class TokenTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateToken()
    {
        $token = new Token('+', TokenType::AdditionOperator);

        $this->assertInstanceOf('MathParser\Lexing\Token', $token);
    }

    public function testCanPrintToken()
    {
        $name = '+';
        $type = TokenType::AdditionOperator;

        $token = new Token($name, $type);
        $string = $token->__toString();

        $this->assertEquals($string, "Token: [$name, $type]");

    }
}
