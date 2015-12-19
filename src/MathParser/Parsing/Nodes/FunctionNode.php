<?php namespace MathParser\Parsing\Nodes;

use MathParser\Interpreting\Visitors\Visitor;

class FunctionNode extends Node
{
    private $name;
    private $operand;

    function __construct($name, Node $operand)
    {
        $this->name = $name;
        $this->operand = $operand;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function getOperand()
    {
        return $this->operand;
    }

    public function accept(Visitor $visitor)
    {
        return $visitor->visitFunctionNode($this);
    }
}
