
<?php

use MathParser\Lexing\Lexer;
use MathParser\Lexing\StdMathLexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\TreePrinter;
use MathParser\Interpreting\LaTeXPrinter;
use MathParser\Interpreting\ASCIIPrinter;
use MathParser\Interpreting\Differentiator;
use MathParser\Interpreting\Evaluator;
use MathParser\Interpreting\RationalEvaluator;

use MathParser\StdMathParser;
use MathParser\RationalMathParser;

include 'vendor/autoload.php';


class ParserWithoutImplicitMultiplication extends Parser {
    protected static function allowImplicitMultiplication() {
        return false;
    }
}

// $lexer = new StdMathLexer();
// $tokens = $lexer->tokenize($argv[1]);
//
// $parser = new ParserWithoutImplicitMultiplication();
// $tree = $parser->parse($tokens);
//
// $treeprinter = new TreePrinter();
// var_dump($tree->accept($treeprinter));
//
// die();


$parser = new RationalMathParser(true);
$parser->setSimplifying(false);

$parser->parse($argv[1]);

$tokens = $parser->getTokenList();
// print_r($tokens);

$tree = $parser->getTree();

echo "Input: ";
$printer = new LaTeXPrinter();
var_dump($tree->accept($printer));

$treeprinter = new TreePrinter();
var_dump($tree->accept($treeprinter));

echo "String conversion: $tree\n";

$ascii = new ASCIIPrinter();
var_dump($tree->accept($ascii));

echo "Derivative: ";

$differentiator = new Differentiator('x');
$derivative = $tree->accept($differentiator);


var_dump($derivative->accept($treeprinter));
var_dump($derivative->accept($printer));

$evaluator = new Evaluator(['x' => 1, 'y' => 1.5 ]);
var_dump($tree->accept($evaluator));

$evaluator = new RationalEvaluator(['x' => '1/3', 'y' => -2]);
var_dump($tree->accept($evaluator));
