<?php namespace MathParser;

use MathParser\Lexing\Lexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Lexing\TokenAssociativity;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\PrettyPrinter;


class MathParser
{
    private $lexer;
    private $parser;
    private $tokens;
    private $tree;

    public function __construct($text)
    {
        $this->lexer = new Lexer();

        $this->lexer->add(new TokenDefinition('/\d+/', TokenType::PosInt, TokenPrecedence::Terminal));

        $this->lexer->add(new TokenDefinition('/sin/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/cos/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/tan/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/cot/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/arcsin|asin/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/arccos|acos/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/arctan|atan/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/arccot|acot/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/exp/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/log|ln/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));
        $this->lexer->add(new TokenDefinition('/lg/', TokenType::FunctionName, TokenPrecedence::FunctionEvaluation));

        $this->lexer->add(new TokenDefinition('/\(/', TokenType::OpenParenthesis, TokenPrecedence::OpenParenthesis));
        $this->lexer->add(new TokenDefinition('/\)/', TokenType::CloseParenthesis, TokenPrecedence::CloseParenthesis));

        $this->lexer->add(new TokenDefinition('/\+/', TokenType::AdditionOperator, TokenPrecedence::BinaryAddition));
        $this->lexer->add(new TokenDefinition('/\-/', TokenType::SubtractionOperator, TokenPrecedence::BinarySubtraction));
        $this->lexer->add(new TokenDefinition('/\*/', TokenType::MultiplicationOperator, TokenPrecedence::BinaryMultiplication));
        $this->lexer->add(new TokenDefinition('/\//', TokenType::DivisionOperator, TokenPrecedence::BinaryDivision));
        $this->lexer->add(new TokenDefinition('/\^/', TokenType::ExponentiationOperator, TokenPrecedence::BinaryExponentiation, TokenAssociativity::Left));

        $this->lexer->add(new TokenDefinition('/pi/', TokenType::Constant, TokenPrecedence::Terminal));
        $this->lexer->add(new TokenDefinition('/e/', TokenType::Constant, TokenPrecedence::Terminal));

        $this->lexer->add(new TokenDefinition('/[a-zA-Z]+/', TokenType::Identifier, TokenPrecedence::Terminal));

        $this->lexer->add(new TokenDefinition('/\n/', TokenType::Terminator, TokenPrecedence::Terminal));
        $this->lexer->add(new TokenDefinition('/\s+/', TokenType::Whitespace, TokenPrecedence::Terminal));

        $this->parser = new Parser();

        $this->tokens = $this->lexer->tokenize($text);
        $this->tree = $this->parser->parse($this->tokens);
    }

    public function getTokenList()
    {
        return $this->tokens;
    }

    public function getTree()
    {
        return $this->tree;
    }
}
