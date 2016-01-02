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

/**
 * AST node representing a number (int or float)
 */
class NumberNode extends Node
{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value
     * @return int|float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitNumberNode($this);
    }
}
