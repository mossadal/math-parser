<?php namespace MathParser\Parsing\Nodes;

use MathParser\Interpreting\Visitors\Visitor;

class VariableNode extends Node
{
    private $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function accept(Visitor $visitor)
    {
        return $visitor->visitVariableNode($this);
    }
}
