<?php namespace MathParser;

use MathParser\Lexing\StdMathLexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Lexing\TokenAssociativity;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\PrettyPrinter;


class StdMathParser
{
    private $lexer;
    private $parser;
    private $tokens;
    private $tree;

    public function __construct($debug=false)
    {
        $this->lexer = new StdMathLexer();
        $this->parser = new Parser();

        $this->parser->debug = $debug;

    }

    public function parse($text)
    {
        $this->tokens = $this->lexer->tokenize($text);
        $this->tree = $this->parser->parse($this->tokens);

        return $this->tree;
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
