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
 * AST node representing a known constant (e.g. pi, e)
 */
class ConstantNode extends Node
{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
    * @property getName
    *
    * Returns the name of the constant
    * @return string
    */
    public function getName()
    {
        return $this->value;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitConstantNode($this);
    }
}
