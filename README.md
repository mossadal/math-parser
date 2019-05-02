# math-parser

[![Latest Stable Version](https://poser.pugx.org/mossadal/math-parser/v/stable)](https://packagist.org/packages/mossadal/math-parser) [![Total Downloads](https://poser.pugx.org/mossadal/math-parser/downloads)](https://packagist.org/packages/mossadal/math-parser) [![License](https://poser.pugx.org/mossadal/math-parser/license)](https://packagist.org/packages/mossadal/math-parser)
[![Code Climate](https://codeclimate.com/github/mossadal/math-parser/badges/gpa.svg)](https://codeclimate.com/github/mossadal/math-parser)

## DESCRIPTION

PHP parser and evaluator library for mathematical expressions.

Intended use: safe and reasonably efficient evaluation of user submitted formulas. The library supports basic arithmetic and elementary functions, as well as variables and extra functions.

The lexer and parser produces an abstract syntax tree (AST) that can be traversed using a tree interpreter. The math-parser library ships with three interpreters:

- an evaluator computing the value of the given expression.
- a differentiator transforming the AST into a (somewhat) simplied AST representing the derivative of the supplied expression.
- a rudimentary LaTeX output generator, useful for pretty printing expressions using MathJax

## EXAMPLES

It is possible to fine-tune the lexer and parser, but the library ships with a StdMathParser class, capable of tokenizing and parsing standard mathematical expressions, including arithmetical operations as well as elementary functions.

By default all single letters are interpreted as variables. Sequences of letters are interpreted as products of variables. Default constants are `e, pi, NAN, INF`. This default can be used as follows.

```{.php}
use MathParser\StdMathParser;

$parser = new StdMathParser();
```

This default can be changed by calling a parser for a new language with newly defined variables and constants:

```{.php}
use MathParser\Lexing\Language;
use MathParser\StdMathParser;

$lang = new Language;
$lang->setVariables(['phi','x','t']);
$lang->setConstants(['h']);

$parser = new StdMathParser($lang);

```


```{.php}
// Generate an abstract syntax tree
$AST = $parser->parse('1+2');

// Do something with the AST, e.g. evaluate the expression:
use MathParser\Interpreting\Evaluator;

$evaluator = new Evaluator();

$value = $AST->accept($evaluator);
echo $value;
```

More interesting example, containing variables:

```{.php}
$AST = $parser->parse('t+sqrt(phi)');

$evaluator->setVariables([ 't' => 2, 'phi' => 3 ]);
$value = $AST->accept($evaluator);
```

We can do other things with the AST. The library ships with a differentiator, computing the (symbolic) derivative with respect to a given variable.

```{.php}
use MathParser\Interpreting\Differentiator;

$differentiator = new Differentiator('x');
$f = $parser->parse('exp(2*x)-x*y');
$df = $f->accept($differentiator);

// $df now contains the AST of '2*exp(x)-y' and can be evaluated further
$evaluator->setVariables([ 't' => 1, 'phi' => 2 ]);
$df->accept($evaluator);
```

We can test whether a term, given as AST, is an instance of another term.

```{.php}
$lang->addVariables(['c','d','u','v']);
$parser = new StdMathParser($lang);
$AST = $parser->parse('x*phi*x');
$AST1 = $parser->parse('(c+d)*(u+v)*(c+d)');
$AST->hasInstance($AST1)['result']; // true
$AST->hasInstance($AST1)['instantiation']['phi']; // AST of u+v

$AST = $parser->parse('x*h*x');
$AST->hasInstance($AST1)['result']; // false as 'h' is a constant and cannot be instantiated
$AST2 = $parser->parse('(c+d)*h*(c+d)');
$AST->hasInstance($AST2)['result']; // true
$AST3 = $parser->parse('h*h*h');
$AST->hasInstance($AST3)['result']; // true
$AST4 = $parser->parse('h*(h*h)');
$AST->hasInstance($AST4)['result']; // false as h*(h*h) is not an instance of x*phi*x=(x*phi)*x
```

### Implicit multiplication

Another helpful feature is that the parser understands implicit multiplication. An expression as `2x` is parsed the same as `2*x` and `xsin(x)cos(x)^2` is parsed as `x*sin(x)*cos(x)^2`.

Note that implicit multiplication has the same precedence as explicit multiplication. In particular, `xy^2z` is parsed as `x*y^2*z` and **not** as `x*y^(2*z)`.

## THANKS

This software is an adaptation of the [math-parser by Frank Wikström](https://github.com/mossadal/math-parser).

The Lexer is based on the lexer described by Marc-Oliver Fiset in his [blog](http://marcofiset.com/programming-language-implementation-part-1-lexer/).

The parser is a version of the "Shunting yard" algorithm, described for example by [Theodore Norvell](http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm#shunting_yard).
