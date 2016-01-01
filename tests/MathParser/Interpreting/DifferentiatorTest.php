<?php

use MathParser\StdMathParser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\PrettyPrinter;
use MathParser\Interpreting\Differentiator;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;

use MathParser\Exceptions\UnknownFunctionException;
use MathParser\Exceptions\UnknownOperatorException;
use MathParser\Exceptions\DivisionByZeroException;

class DifferentiatorTest extends PHPUnit_Framework_TestCase
{
    private $parser;
    private $differentiator;

    public function setUp()
    {
        $this->parser = new StdMathParser();
        $this->differentiator = new Differentiator('x');
    }

    public function diff($node)
    {
        $derivative = $node->accept($this->differentiator);

        return $derivative;
    }

    private function assertNodesEqual($node1, $node2)
    {
        $message = "Node1: ".var_export($node1,true)."\nNode 2: ".var_export($node2, true)."\n";

        $this->assertTrue(Node::compareNodes($node1, $node2), $message);
    }

    private function assertResult($f, $df)
    {
        $fnc = $this->parser->parse($f);
        $derivative = $this->parser->parse($df);

        $this->assertNodesEqual($this->diff($fnc), $derivative);
    }

    public function testCanDifferentiateVariable()
    {
        $this->assertResult('x', '1');
        $this->assertResult('y', '0');
    }

    public function testCanDifferentiateConstant()
    {
        $this->assertResult('pi', '0');
        $this->assertResult('pi*e', '0');
        $this->assertResult('7', '0');
        $this->assertResult('1+3', '0');
        $this->assertResult('5*2', '0');
        $this->assertResult('1/2', '0');
        $this->assertResult('2^2', '0');
        $this->assertResult('-2', '0');
    }

    public function testCanDifferentiateExp()
    {
        $this->assertResult('exp(x)', 'exp(x)');
    }


    public function testCanDifferentiateLog()
    {
        $this->assertResult('log(x)', '1/x');
    }

    public function testCanDifferentiateLog10()
    {
        $this->assertResult('log10(x)', '1/(log(10)x)');
    }

    public function testCanDifferentiateSin()
    {
        $this->assertResult('sin(x)', 'cos(x)');
    }

    public function testCanDifferentiateCos()
    {
        $this->assertResult('cos(x)', '-sin(x)');
    }

    public function testCanDifferentiateTan()
    {
        $this->assertResult('tan(x)', '1+tan(x)^2');
    }

    public function testCanDifferentiateCot()
    {
        $this->assertResult('cot(x)', '~1-cot(x)^2');
;
    }

    public function testCanDifferentiateArcsin()
    {
        $this->assertResult('arcsin(x)', '1/sqrt(1-x^2)');
    }

    public function testCanDifferentiateArccos()
    {
        $this->assertResult('arccos(x)', '~1/sqrt(1-x^2)');
    }

    public function testCanDifferentiateArctan()
    {
        $this->assertResult('arctan(x)', '1/(1+x^2)');
        $this->assertResult('arctan(x^3)', '(3x^2)/(1+x^6)');
    }

    public function testCanDifferentiateArccot()
    {
        $this->assertResult('-arccot(x)', '1/(1+x^2)');
    }

    public function testCanDifferentiateSqrt()
    {
        $this->assertResult('sqrt(x)', '1/(2sqrt(x))');
    }

    public function testCanDifferentiateSum()
    {
        $this->assertResult('x+sin(x)', '1+cos(x)');
        $this->assertResult('sin(x)+y', 'cos(x)');
        $this->assertResult('y+sin(x)', 'cos(x)');
    }

    public function testCanDifferentiateDifference()
    {
        $this->assertResult('x+sin(x)', '1-cos(x)');
        $this->assertResult('sin(x)-y', 'cos(x)');
        $this->assertResult('sin(x)-sin(x)', '0');
    }

    public function testCanDifferentiateProduct()
    {
        $this->assertResult('x*sin(x)', 'x*cos(x)+sin(x)');
    }

    public function testCanDifferentiateExponent()
    {
        $this->assertResult('x^1', '1');
        $this->assertResult('x^2', '2x');
        $this->assertResult('x^3', '3x^2');
        $this->assertResult('x^x', 'x^x*(log(x)+1)');
    }

    public function testCanDifferentiateQuotient()
    {
        $this->assertResult('x/sin(x)', '(sin(x)-x*cos(x))/sin(x)^2');
        $this->assertResult('x/1', '1');


        $f = $this->parser->parse('x/0');
        $this->setExpectedException(DivisionByZeroException::class);
        $this->diff($f);
    }

    public function testCanDifferentiateComposite()
    {
        $this->assertResult('sin(sin(x))', 'cos(x)*cos(sin(x))');

    }

    public function testCanDifferentiateUnaryMinus()
    {
        $this->assertResult('-x', '~1');
    }

    public function testCannotDifferentiateUnknownFunction()
    {
        $node = new FunctionNode('erf', new VariableNode('x'));
        $this->setExpectedException(UnknownFunctionException::class);

        $this->diff($node);

    }

    public function testCannotDifferentiateUnknownOperator()
    {
        $node = new ExpressionNode(new NumberNode(1), '%', new VariableNode('x'));
        $this->setExpectedException(UnknownOperatorException::class);

        $this->diff($node);

    }

    public function testCanDifferentiateHyperbolicFunctions()
    {
        $this->assertResult('sinh(x)', 'cosh(x)');
        $this->assertResult('cosh(x)', 'sinh(x)');
        $this->assertResult('tanh(x)', '1-tanh(x)^2');
        $this->assertResult('coth(x)', '1-coth(x)^2');

        $this->assertResult('arsinh(x)', '1/sqrt(x^2+1)');
        $this->assertResult('arcosh(x)', '1/sqrt(x^2-1)');
        $this->assertResult('artanh(x)', '1/(1-x^2)');
        $this->assertResult('arcoth(x)', '1/(1-x^2)');
    }
}
