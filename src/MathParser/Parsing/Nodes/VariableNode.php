<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>, modified by Ingo Dahn <dahn@dahn-research.eu>
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
    /** string $name Name of represented variable, e.g. 'x' */
    private $name;

    /** Constructor. Create a VariableNode with a given variable name. */
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

    /** Implementing the compareTo abstract method. */
    public function compareTo($other)
    {
        if ($other === null) {
            return false;
        }
        if (!($other instanceof VariableNode)) {
            return false;
        }

        return $this->getName() == $other->getName();
    }

    /** Implementing the hasInstance abstract method. */
    public function hasInstance($other,$inst=[])
    {
        if ($other === null) {
            return ['result' =>false];
        }
        $name=$this->getName();
        /*
        if (in_array($name,$consts)) {
            if (! ($other instanceof VariableNode)){
                return ['result' => false];
            } elseif ($other->getName() == $name) {
                return ['result' => true, 'instantiation' => $vars];
            } else {
                return ['result' => false];
            }
        }
        */
        if (array_key_exists($name,$inst)) {
            if ($other->compareTo($inst[$name])) {
                return ['result' => true, 'instantiation' =>$inst];
            } else {
                return ['result' => false];
            }
        }
        $inst[$name]=$other;

        return ['result' => true, 'instantiation' => $inst];
    }

}
