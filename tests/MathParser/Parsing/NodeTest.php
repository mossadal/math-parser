<?php

use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ConstantNode;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\SubExpressionNode;
use MathParser\Parsing\Nodes\VariableNode;

use MathParser\Interpreting\TreePrinter;
use MathParser\StdMathParser;


class NodeTest extends PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new StdMathParser();
    }

    public function testCanCompareSubExpressionNodes()
    {
        $node = new SubExpressionNode('%');
        $other = new NumberNode(1);

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertFalse($node->compareTo(new SubExpressionNode('$')));
    }

    public function testCanCompareConstantNodes()
    {
        $node = new ConstantNode('pi');
        $other = new NumberNode(1);

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertFalse($node->compareTo(new ConstantNode('e')));
    }

    public function testCanCompareFunctionNodes()
    {
        $node = new FunctionNode('sin', new VariableNode('x'));
        $other = new NumberNode(1);

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertFalse($node->compareTo(new FunctionNode('cos', new VariableNode('x'))));
    }

    public function testCanCompareVariableNodes()
    {
        $node = new VariableNode('x');
        $other = new NumberNode(1);

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertFalse($node->compareTo(new VariableNode('y')));
    }

    public function testCanCompareNumberNodes()
    {
        $node = new NumberNode(3);
        $other = new VariableNode('x');

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertFalse($node->compareTo(new NumberNode(7)));
    }

    public function testCanCompareExpressionNodes()
    {
        $node = new ExpressionNode(new VariableNode('x'), '+', new VariableNode('y'));
        $node2 = new ExpressionNode(new VariableNode('x'), '-', new VariableNode('y'));
        $node3 = new ExpressionNode(new VariableNode('x'), '-', null);
        $node4 = new ExpressionNode(null, '-', new VariableNode('y'));
        $other = new VariableNode('x');

        $this->assertFalse($node->compareTo(null));
        $this->assertFalse($node->compareTo($other));
        $this->assertTrue($node->compareTo($node));
        $this->assertTrue($node2->compareTo($node2));
        $this->assertTrue($node3->compareTo($node3));
        $this->assertTrue($node4->compareTo($node4));

        $this->assertFalse($node->compareTo($node2));
        $this->assertFalse($node->compareTo($node3));
        $this->assertFalse($node->compareTo($node4));
        $this->assertFalse($node2->compareTo($node3));
        $this->assertFalse($node2->compareTo($node4));
        $this->assertFalse($node3->compareTo($node4));
        $this->assertFalse($node2->compareTo($node4));

    }

    public function testCanComputeComplexity()
    {
        $node = new NumberNode(1);
        $this->assertEquals($node->complexity(), 1);

        $node = new VariableNode('x');
        $this->assertEquals($node->complexity(), 1);

        $node = new ConstantNode('pi');
        $this->assertEquals($node->complexity(), 1);

        $f = $this->parser->parse('x+y');
        $this->assertEquals($f->complexity(), 4);

        $f = $this->parser->parse('x-y');
        $this->assertEquals($f->complexity(), 4);

        $f = $this->parser->parse('x*y');
        $this->assertEquals($f->complexity(), 4);

        $f = $this->parser->parse('x/y');
        $this->assertEquals($f->complexity(), 6);

        $f = $this->parser->parse('x^y');
        $this->assertEquals($f->complexity(), 10);

        $f = $this->parser->parse('sin(x)');
        $this->assertEquals($f->complexity(), 6);

        $f = $this->parser->parse('x + sin(x^2)');
        $this->assertEquals($f->complexity(), 18);

        $node = new SubExpressionNode('(');
        $this->assertEquals($node->complexity(), 1000);
    }


    public function testCanCreateSubExpressionNode()
    {
        $node = new SubExpressionNode('%');
        $this->assertEquals($node->getValue(), '%');
        $this->assertNull($node->accept(new TreePrinter()));
    }


}
