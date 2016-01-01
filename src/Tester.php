<?php

use MathParser\Lexing\Lexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\TreePrinter;
use MathParser\Interpreting\LaTeXPrinter;
use MathParser\Interpreting\Differentiator;
use MathParser\StdMathParser;

include 'vendor/autoload.php';

$parser = new StdMathParser(true);

$parser->parse($argv[1]);

//$tokens = $parser->getTokenList();
//var_dump($tokens);



$tree = $parser->getTree();

// $interpreter = new Interpreter();
// $result = $tree->accept($interpreter);

echo "Input: ";
$printer = new LaTeXPrinter();
var_dump($tree->accept($printer));

$treeprinter = new TreePrinter();
var_dump($tree->accept($treeprinter));

echo "Derivative: ";

$differentiator = new Differentiator('x');
$derivative = $tree->accept($differentiator);

var_dump($derivative->accept($printer));
var_dump($derivative->accept($treeprinter));
