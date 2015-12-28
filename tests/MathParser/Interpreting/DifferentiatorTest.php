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

    public function testCanDifferentiateVariable()
    {
        $f = $this->parser->parse('x');
        $df = $this->parser->parse('1');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('y');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateConstant()
    {
        $f = $this->parser->parse('pi');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('pi*e');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('7');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('1+3');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('5*2');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('1/2');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('2^2');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);

    }

    public function testCanDifferentiateExp()
    {
        $f = $this->parser->parse('exp(x)');
        $this->assertNodesEqual($this->diff($f), $f);
    }


    public function testCanDifferentiateLog()
    {
        $f = $this->parser->parse('log(x)');
        $df = $this->parser->parse('1/x');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateLog10()
    {
        $f = $this->parser->parse('log10(x)');
        $df = $this->parser->parse('1/(log(10)x)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateSin()
    {
        $f = $this->parser->parse('sin(x)');
        $df = $this->parser->parse('cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateCos()
    {
        $f = $this->parser->parse('cos(x)');
        $df = $this->parser->parse('-sin(x)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateTan()
    {
        $f = $this->parser->parse('tan(x)');
        $df = $this->parser->parse('1+tan(x)^2');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateCot()
    {
        $f = $this->parser->parse('cot(x)');
        $df = $this->parser->parse('~1-cot(x)^2');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateArcsin()
    {
        $f = $this->parser->parse('arcsin(x)');
        $df = $this->parser->parse('1/sqrt(1-x^2)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateArccos()
    {
        $f = $this->parser->parse('arccos(x)');
        $df = $this->parser->parse('~1/sqrt(1-x^2)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateArctan()
    {
        $f = $this->parser->parse('arctan(x)');
        $df = $this->parser->parse('1/(1+x^2)');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('arctan(x^3)');
        $df = $this->parser->parse('(3x^2)/(1+x^6)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateArccot()
    {
        $f = $this->parser->parse('-arccot(x)');
        $df = $this->parser->parse('1/(1+x^2)');

        $this->assertNodesEqual($this->diff($f), $df);

    }

    public function testCanDifferentiateSqrt()
    {
        $f = $this->parser->parse('sqrt(x)');
        $df = $this->parser->parse('1/(2sqrt(x))');

        $this->assertNodesEqual($this->diff($f), $df);

    }

    public function testCanDifferentiateSum()
    {
        $f = $this->parser->parse('x+sin(x)');
        $df = $this->parser->parse('1+cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('sin(x)+y');
        $df = $this->parser->parse('cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('y+sin(x)');
        $df = $this->parser->parse('cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateDifference()
    {
        $f = $this->parser->parse('x-sin(x)');
        $df = $this->parser->parse('1-cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('sin(x)-y');
        $df = $this->parser->parse('cos(x)');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('sin(x)-sin(x)');
        $df = $this->parser->parse('0');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateProduct()
    {
        $f = $this->parser->parse('x*sin(x)');
        $df = $this->parser->parse('x*cos(x)+sin(x)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateExponent()
    {
        $f = $this->parser->parse('x^1');
        $df = $this->parser->parse('1');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('x^2');
        $df = $this->parser->parse('2x');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('x^3');
        $df = $this->parser->parse('3x^2');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('x^x');
        $df = $this->parser->parse('x^x*(log(x)+1)');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateQuotient()
    {
        $f = $this->parser->parse('x/sin(x)');
        $df = $this->parser->parse('(sin(x)-x*cos(x))/sin(x)^2');

        $this->assertNodesEqual($this->diff($f), $df);

        $f = $this->parser->parse('x/1');
        $df = $this->parser->parse('1');

        $this->assertNodesEqual($this->diff($f), $df);


        $f = $this->parser->parse('x/0');
        $this->setExpectedException(DivisionByZeroException::class);
        $this->diff($f);
    }

    public function testCanDifferentiateComposite()
    {
        $f = $this->parser->parse('sin(sin(x))');
        $df = $this->parser->parse('cos(x)*cos(sin(x))');

        $this->assertNodesEqual($this->diff($f), $df);
    }

    public function testCanDifferentiateUnaryMinus()
    {
        $f = $this->parser->parse('-x');
        $df = $this->parser->parse('-1');

        $this->assertNodesEqual($this->diff($f), $df);
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

}
