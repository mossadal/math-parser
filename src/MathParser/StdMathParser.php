<?php namespace MathParser;

use MathParser\Lexing\StdMathLexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Lexing\TokenAssociativity;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\PrettyPrinter;

/**
 * Convenience class for using the MathParser library.
 *
 * StdMathParser is a wrapper for the StdMathLexer and Parser
 * classes, and if you do not require any tweaking, this is the
 * most straightforward way to use the MathParser library.
 *
 * ### Example usage:
 *
 * ~~~{.php}
 * use MathParser\StdMathParser;
 * use MathParser\Interpreting\Evaluator;
 * use MathParser\Interpreting\Differentiator;
 *
 * $parser = new StdMathParser();
 * $AST = $parser->parse('2x + 2y^2/sin(x)');
 *
 * // Do whatever you want with the parsed expression,
 * // for example evaluate it.
 * $evaluator = new Evaluator([ 'x' => 1, 'y' => 2 ]);
 * $value = $AST->accept($evaluator);
 *
 * // or differentiate it:
 * $d_dx = new Differentiator('x');
 * $derivative = $AST->accept($d_dx);
 * $valueOfDerivative = $derivative->accept($evaluator);
 * ~~~
 *
 */
class StdMathParser
{
    private $lexer;
    private $parser;
    private $tokens;
    private $tree;

    public function __construct()
    {
        $this->lexer = new StdMathLexer();
        $this->parser = new Parser();
    }

    /**
     * Parse the given mathematical expression into an abstract syntax tree.
     *
     * @param string $text Input
     * @return Node
     */
    public function parse($text)
    {
        $this->tokens = $this->lexer->tokenize($text);
        $this->tree = $this->parser->parse($this->tokens);

        return $this->tree;
    }

    /**
     * Return the token list for the last parsed expression.
     * @return Token[]
     */
    public function getTokenList()
    {
        return $this->tokens;
    }

    /**
     * Return the AST of the last parsed expression.
     * @return Node
     */
    public function getTree()
    {
        return $this->tree;
    }
}
