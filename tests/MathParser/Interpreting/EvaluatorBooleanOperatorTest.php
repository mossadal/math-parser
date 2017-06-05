<?php

use MathParser\Interpreting\Evaluator;
use MathParser\Interpreting\Interpreter;
use MathParser\RationalMathParser;
use MathParser\StdMathParser;

use MathParser\Interpreting\ASCIIPrinter;
class EvaluatorBooleanOperatorTest extends PHPUnit_Framework_TestCase
{
    private $parser;
    private $rparser;

    private $evaluator;
    private $variables;


    public function setUp()
    {
        $this->parser = new StdMathParser();
        $this->rparser = new RationalMathParser();

        $this->variables = array('x' => '0.7', 'y' => '2.1');
        $this->evaluator = new Evaluator($this->variables);
    }

    private function evaluate($f)
    {
        $this->evaluator->setVariables($this->variables);
        return $f->accept($this->evaluator);
    }

    private function assertResult($f, $x)
    {
        $value = $this->evaluate($this->parser->parse($f));
        $this->assertEquals($x, $value);
    }


    public function testNumberExpressionWithEqualOperator() {
        $this->assertResult('10=10', 1);
        $this->assertResult('(10+8+2)=(10+10)', 1);
        $this->assertResult('10+8+2=10+10', 1);
        $this->assertResult('4/3=4/3', 1);
        $this->assertResult('4=5', 0);
    }



    public function testNumberExpressionWithAndOperator() {
        $this->assertResult('1 && 1', 1);
        $this->assertResult('1 && 0', 0);
        $this->assertResult('0 && 0', 0);
        $this->assertResult('2.2 && 1', 1);
    }


    public function testOrOperatorIsValid() {
        $expression = $this->parser->parse('1 || 1');
        $this->assertNotNull($expression);
    }

    public function testNumberExpressionWithOrOperator() {
        $this->assertResult('1 || 1', 1);
        $this->assertResult('1 || 0', 1);
        $this->assertResult('0 || 0', 0);
        $this->assertResult('2.2 || 1', 1);
    }

    public function testNotFunctionIsValid() {
        $expression = $this->parser->parse('!1');
        $this->assertNotNull($expression);
    }


    public function testNotOperatorWithIntegerValue() {
        $this->assertResult('!1', 0);
        $this->assertResult('!0', 1);
        $this->assertResult('!(0+1)', 0);
        $this->assertResult('!(!0)', 0);
    }

    public function testNotOperatorWithFloatValue() {

        $this->assertResult('!(0.1)', 0);
        $this->assertResult('!x', 0);
        $this->assertResult('!(x+y)', 0);
    }

    public function testGreaterOperatorValidSyntax() {
        $expression = $this->parser->parse('3 > 1');
        $this->assertNotNull($expression);
    }

    public function testGreaterOperator() {
        $this->assertResult('3 > 1', 1);
        $this->assertResult('1 > 3', 0);
        $this->assertResult('1.1 > 3', 0);
        $this->assertResult('3.1 > 3', 1);
    }


}
