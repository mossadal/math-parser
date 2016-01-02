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
 * AST node representing a variable
 */
class VariableNode extends Node
{
    private $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Return the name of the variable
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitVariableNode($this);
    }
}
