<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

 namespace MathParser\Parsing\Nodes;

use MathParser\Interpreting\Visitors\Visitor;

class FunctionNode extends Node
{
    private $name;
    private $operand;

    function __construct($name, $operand)
    {
        $this->name = $name;
        if (is_int($operand)) $operand = new NumberNode($operand);
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
