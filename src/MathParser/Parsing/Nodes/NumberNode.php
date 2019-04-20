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
    /** int|float $value The value of the represented number. */
    private $value;

    /** Constructor. Create a NumberNode with given value. */
    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value
     * @retval int|float
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

    /** Implementing the compareTo abstract method. */
    public function compareTo($other)
    {
        if ($other === null) {
            return false;
        }
        if (!($other instanceof NumberNode)) {
            return false;
        }

        return $this->getValue() == $other->getValue();
    }

    /** Implementing the hasInstance abstract method. */
    public function hasInstance($other,$consts=[],$vars=[])
    {
        if ($other === null) {
            return ['result' => false];
        }
        if (!($other instanceof NumberNode)) {
            return ['result' => false];
        }

        if (! ($this->getValue() == $other->getValue())) {
            return ['result' => false];
        }
        return ['result' => true, 'instantiation' => $vars];
    }

}
