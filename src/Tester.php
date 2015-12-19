<?php

use MathParser\Lexing\Lexer;
use MathParser\Lexing\TokenDefinition;
use MathParser\Lexing\TokenType;
use MathParser\Parsing\Parser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\PrettyPrinter;
use MathParser\MathParser;

include 'vendor/autoload.php';


$parser = new MathParser($argv[1]);

$tokens = $parser->getTokenList();
//var_dump($tokens);


$tree = $parser->getTree();


// $interpreter = new Interpreter();
// $result = $tree->accept($interpreter);

$printer = new PrettyPrinter();
var_dump($tree->accept($printer));

//var_dump($result);
