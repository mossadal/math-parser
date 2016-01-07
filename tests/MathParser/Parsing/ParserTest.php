<?php

use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Parsing\Parser;
use MathParser\Lexing\StdMathLexer;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\SubExpressionNode;

use MathParser\Interpreting\TreePrinter;

use MathParser\Exceptions\SyntaxErrorException;
use MathParser\Exceptions\UnknownOperatorException;

class ParserWithoutImplicitMultiplication extends Parser {
    protected static function allowImplicitMultiplication() {
        return false;
    }
}

class ParserTest extends PHPUnit_Framework_TestCase
{
    private function createNumberToken($x)
    {
        return new Token("$x", TokenType::PosInt);
    }

    private function createVariableToken($x)
    {
        return new Token("$x", TokenType::Identifier);
    }

    private function createAdditionToken()
    {
        return new Token('+', TokenType::AdditionOperator);
    }

    private function createMultiplcationToken()
    {
        return new Token('*', TokenType::MultiplicationOperator);
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

    private function assertVariableNode($node, $value)
    {
        $this->assertInstanceOf('MathParser\Parsing\Nodes\VariableNode', $node);
        $this->assertEquals($value, $node->getName());
    }

    public function testCanParseSingleNumberExpression()
    {
        $tokens = [
            $this->createNumberToken(3)
        ];

        $node = $this->parse($tokens);

        $this->assertNumberNode($node, 3);
    }


    public function testCanParseBinaryExpression()
    {
        $tokens = [
            $this->createNumberToken(3),
            $this->createAdditionToken(),
            $this->createNumberToken(5)
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
            $this->createNumberToken(3),
            $this->createAdditionToken(),
            $this->createNumberToken(5),
            $this->createMultiplcationToken(),
            $this->createNumberToken(7)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertNumberNode($node->getLeft(), 3);
        $this->assertEquals('+', $node->getOperator());

        $factors = $node->getRight();
        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $factors);
        $this->assertNumberNode($factors->getLeft(), 5);
        $this->assertEquals('*', $factors->getOperator());
        $this->assertNumberNode($factors->getRight(), 7);
    }

    public function testCanParseWithCorrectPrecedence2()
    {
        $tokens = [
            $this->createNumberToken(3),
            $this->createMultiplcationToken(),
            $this->createNumberToken(5),
            $this->createAdditionToken(),
            $this->createNumberToken(7)
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertNumberNode($node->getRight(), 7);
        $this->assertEquals('+', $node->getOperator());

        $factors = $node->getLeft();
        $this->assertInstanceOf('MathParser\Parsing\Nodes\ExpressionNode', $factors);
        $this->assertNumberNode($factors->getLeft(), 3);
        $this->assertEquals('*', $factors->getOperator());
        $this->assertNumberNode($factors->getRight(), 5);

    }

    public function testImplicitMultiplicationWithNumbers()
    {
        $tokens = [
            $this->createNumberToken('3'),
            $this->createNumberToken('5')
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('\MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertEquals('*', $node->getOperator());
        $this->assertNumberNode($node->getLeft(), 3);
        $this->assertNumberNode($node->getRight(), 5);
    }

    public function testImplicitMultiplicationWithVariables()
    {
        $tokens = [
            $this->createVariableToken('x'),
            $this->createVariableToken('y')
        ];

        $node = $this->parse($tokens);

        $this->assertInstanceOf('\MathParser\Parsing\Nodes\ExpressionNode', $node);

        $this->assertEquals('*', $node->getOperator());
        $this->assertVariableNode($node->getLeft(), 'x');
        $this->assertVariableNode($node->getRight(), 'y');
    }

    public function testParserWithoutImplicitMultiplication()
    {
        $lexer = new StdMathLexer();
        $tokens = $lexer->tokenize('2x');

        $parser = new ParserWithoutImplicitMultiplication();

        $this->setExpectedException(SyntaxErrorException::class);
        $node = $parser->parse($tokens);
    }

    public function testUnknownException()
    {
        $this->setExpectedException(UnknownOperatorException::class);
        $node = new ExpressionNode(null, '%', null);
    }

    public function testCanCreateTemporaryUnaryMinusNode()
    {
        $node = new ExpressionNode(null, '~', null);
        $this->assertEquals($node->getOperator(), '~');
        $this->assertNull($node->getRight());
        $this->assertNull($node->getLeft());
        $this->assertEquals($node->getPrecedence(), 25);
    }

    public function testCanCreateSubExpressionNode()
    {
        $node = new SubExpressionNode('%');
        $this->assertEquals($node->getValue(), '%');

        $this->assertNull($node->accept(new TreePrinter()));
    }
}
