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
 * AST node representing a postfix operator. Only for temporary
 * use in the parser. The node will be converted to a FunctionNode
 * when consumed by the parser.
 */
class PostfixOperatorNode extends Node
{
    /** string $name Name of the postfix operator. Currently, only '!' is possible. */
    private $name;

    /** Constructor. Create a PostfixOperatorNode with given value. */
    function __construct($name)
    {
        $this->name = $name;
    }


    /** returns the name of the postfix operator */
    public function getOperator()
    {
        return $this->name;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return null;
    }

    /** Implementing the compareTo abstract method. */
    public function compareTo($other)
    {
        if ($other === null) {
            return false;
        }
        if (!($other instanceof PostfixOperatorNode)) {
            return false;
        }

        return $this->getOperator() == $other->getOperator();
    }

    /** Implementing the hasInstance abstract method. */
    public function hasInstance($other,$inst=[])
    {
        if ($other === null) {
            return ['result' => false];
        }
        if (!($other instanceof PostfixOperatorNode)) {
            return ['result' => false];
        }
        if (! $this->getOperator() == $other->getOperator()) {
            return ['result' => false];
        }
        return ['result' => true, 'instantiation' => $inst];
    }

}
