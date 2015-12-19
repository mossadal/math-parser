<?php

use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testCanParseSingleNumberExpression()
    {
        $tokens = [
            new Token('3', TokenType::Number)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);
        $this->assertNumberNode($node->getLeft(), 3);
    }

    private function parse($tokens)
    {
        $parser = new Parser();
        return $parser->parse($tokens);
    }

    private function assertNumberNode($node, $value)
    {
        $this->assertInstanceOf('MathParser\Parsing\Nodes\NumberNode', $node);
        $this->assertEquals($value, $node->getValue());
    }

    public function testCanParseBinaryExpression()
    {
        $tokens = [
            new Token('3', TokenType::Number),
            new Token('+', TokenType::Operator),
            new Token('5', TokenType::Number)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertNumberNode($node->getLeft(), 3);
        $this->assertEquals('+', $node->getOperator());
        $this->assertNumberNode($node->getRight(), 5);
    }

    public function testCanParseWithCorrectPrecedence()
    {
        $tokens = [
            new Token('3', TokenType::Number),
            new Token('+', TokenType::Operator),
            new Token('5', TokenType::Number),
            new Token('*', TokenType::Operator),
            new Token('7', TokenType::Number)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertNumberNode($node->getLeft(), 3);
        $this->assertEquals('+', $node->getOperator());

        $factors = $node->getRight();
        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $factors);
        $this->assertNumberNode($factors->getLeft(), 5);
        $this->assertEquals('*', $node->getOperator());
        $this->assertNumberNode($factors->getRight(), 7);
    }

    public function testCanParseWithCorrectPrecedence2()
    {
        $tokens = [
            new Token('3', TokenType::Number),
            new Token('*', TokenType::Operator),
            new Token('5', TokenType::Number),
            new Token('+', TokenType::Operator),
            new Token('7', TokenType::Number)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertNumberNode($node->getRight(), 7);
        $this->assertEquals('+', $node->getOperator());

        $factors = $node->getLeft();
        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $factors);
        $this->assertNumberNode($factors->getLeft(), 3);
        $this->assertEquals('*', $node->getOperator());
        $this->assertNumberNode($factors->getRight(), 5);

    }

}
