<?php

use MathParser\StdMathParser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\Evaluator;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ConstantNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ExpressionNode;


use MathParser\Exceptions\UnknownVariableException;
use MathParser\Exceptions\UnknownConstantException;
use MathParser\Exceptions\UnknownFunctionException;
use MathParser\Exceptions\UnknownOperatorException;
use MathParser\Exceptions\DivisionByZeroException;

class EvaluatorTest extends PHPUnit_Framework_TestCase
{
    private $parser;
    private $evaluator;
    private $variables;

    public function setUp()
    {
        $this->parser = new StdMathParser();

        $this->variables = array('x' => '0.7', 'y' => '2.1');
        $this->evaluator = new Evaluator($this->variables);
    }

    private function evaluate($f)
    {
        $this->evaluator->setVariables($this->variables);
        return $f->accept($this->evaluator);
    }

    public function testCanEvaluateNumber()
    {
        $f = $this->parser->parse("3");
        $value = $this->evaluate($f);

        $this->assertEquals($value, 3);

        $f = $this->parser->parse("(-2)");
        $value = $this->evaluate($f);

        $this->assertEquals($value, -2);
    }

    public function testCanEvaluateConstant()
    {
        $f = $this->parser->parse("pi");
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi());

        $f = $this->parser->parse("e");
        $value = $this->evaluate($f);

        $this->assertEquals($value, exp(1));

        $f = new ConstantNode('sdf');
        $this->setExpectedException(UnknownConstantException::class);
        $value = $this->evaluate($f);
    }

    public function testCanEvaluateVariable()
    {
        $f = $this->parser->parse("x");
        $value = $this->evaluate($f);

        $this->assertEquals($value, $this->variables['x']);

        $this->setExpectedException(UnknownVariableException::class);

        $f = $this->parser->parse("q");
        $value = $this->evaluate($f);
    }

    public function testCanEvaluateAdditiion()
    {
        $f = $this->parser->parse('3+5');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 8);

        $f = $this->parser->parse('3+5+1');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 9);
    }

    public function testCanEvaluateSubtraction()
    {
        $f = $this->parser->parse('3-5');
        $value = $this->evaluate($f);

        $this->assertEquals($value, -2);

        $f = $this->parser->parse('3-5-1');
        $value = $this->evaluate($f);

        $this->assertEquals($value, -3);

    }

    public function testCanEvaluateMultiplication()
    {
        $f = $this->parser->parse('3*5');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 15);
    }

    public function testCanEvaluateDivision()
    {
        $f = $this->parser->parse('3/5');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0.6);

        $f = $this->parser->parse('20/2/5');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 2);
    }

    public function testCannotDivideByZero()
    {
        $f = $this->parser->parse('3/0');

        $this->setExpectedException(DivisionByZeroException::class);
        $value = $this->evaluate($f);
    }


    public function testCanEvaluateExponentiation()
    {
        $f = $this->parser->parse('2^3');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 8);

        $f = $this->parser->parse('2^3^2');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 512);

        $f = $this->parser->parse('0^0');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 1);

        $f = $this->parser->parse('(-1)^(-1)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, -1);
    }

    public function testExponentiationExceptions()
    {
        $f = $this->parser->parse('0^(-1)');
        $value = $this->evaluate($f);

        $this->assertTrue(is_infinite($value));

        $f = $this->parser->parse('(-1)^(1/2)');
        $value = $this->evaluate($f);

        $this->assertTrue(is_nan($value));
    }

    public function testCanEvaluateSine()
    {
        $f = $this->parser->parse('sin(pi)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0);

        $f = $this->parser->parse('sin(pi/2)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 1);

        $f = $this->parser->parse('sin(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, sin($this->variables['x']));
    }

    public function testCanEvaluateCosine()
    {
        $f = $this->parser->parse('cos(pi)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, -1);

        $f = $this->parser->parse('cos(pi/2)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0);

        $f = $this->parser->parse('cos(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, cos($this->variables['x']));
    }

    public function testCanEvaluateTangent()
    {
        $f = $this->parser->parse('tan(pi)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0);

        $f = $this->parser->parse('tan(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, tan($this->variables['x']));
    }

    public function testCanEvaluateCotangent()
    {
        $f = $this->parser->parse('cot(pi/2)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0);

        $f = $this->parser->parse('cot(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 1/tan($this->variables['x']));
    }

    public function testCanEvaluateArcsin()
    {
        $f = $this->parser->parse('arcsin(1)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi()/2);

        $f = $this->parser->parse('arcsin(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, asin($this->variables['x']));

        $f = $this->parser->parse('arcsin(2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);

    }

    public function testCanEvaluateArccos()
    {
        $f = $this->parser->parse('arccos(0)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi()/2);

        $f = $this->parser->parse('arccos(1)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, 0);

        $f = $this->parser->parse('arccos(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, acos($this->variables['x']));

        $f = $this->parser->parse('arccos(2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);

    }

    public function testCanEvaluateArctan()
    {
        $f = $this->parser->parse('arctan(1)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi()/4);


        $f = $this->parser->parse('arctan(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, atan($this->variables['x']));
    }

    public function testCanEvaluateArccot()
    {
        $f = $this->parser->parse('arccot(1)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi()/4);


        $f = $this->parser->parse('arccot(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, pi()/2-atan($this->variables['x']));
    }

    public function testCanEvaluateExp()
    {
        $f = $this->parser->parse('exp(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, exp($this->variables['x']));
    }

    public function testCanEvaluateLog()
    {
        $f = $this->parser->parse('log(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, log($this->variables['x']));

        $f = $this->parser->parse('log(-1)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);

    }

    public function testCanEvaluateLog10()
    {
        $f = $this->parser->parse('log10(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, log($this->variables['x'])/log(10));
    }

    public function testCanEvaluateSqrt()
    {
        $f = $this->parser->parse('sqrt(x)');
        $value = $this->evaluate($f);

        $this->assertEquals($value, sqrt($this->variables['x']));

        $f = $this->parser->parse('sqrt(-2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);

    }

    public function testCanIdentifyUnknownFunction()
    {
        $f = new FunctionNode('sdf', new NumberNode(1));

        $this->setExpectedException(UnknownFunctionException::class);
        $value = $this->evaluate($f);

    }

    public function testCanIdentifyUnknownOperator()
    {
        $f = new ExpressionNode(new NumberNode(2), '@', new NumberNode(1));

        $this->setExpectedException(UnknownOperatorException::class);
        $value = $this->evaluate($f);

    }
}
