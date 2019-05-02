<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>, modified by Ingo Dahn <dahn@dahn-research.eu>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

namespace MathParser\Parsing\Nodes;

use MathParser\Lexing\Language;
use MathParser\Interpreting\Visitors\Visitor;

/**
 * AST node representing a known constant (e.g. pi, e)
 */
class ConstantNode extends Node
{
    /**
     * Name of the constant, e.g. 'pi' or 'e'.
     *
     * string $value
     **/
    private $value;

    /**
     * Constructor.
     *
     * ### Example
     *
     * ~~~{.php}
     * $node = new ConstantNode('pi');
     * ~~~
     *
     */
    function __construct($value, $lang = null)
    {
        if ($lang === null) {
            $lang = new Language;
        }
        $this->language = $lang;
        $this->value = $value;
    }

    /**
    * @property getName
    *
    * Returns the name of the constant
    * @retval string
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

    /** Implementing the compareTo abstract method. */
    public function compareTo($other)
    {
        if ($other === null) {
            return false;
        }
        if (!($other instanceof ConstantNode)) {
            return false;
        }

        return $this->getName() == $other->getName();
    }

    /** Implementing the hasInstance abstract method. */
    public function hasInstance($other,$inst=[])
    {
        if ($other === null) {
            return ['result' => false];
        }
        if (!($other instanceof ConstantNode)) {
            return ['result' => false];
        }
        $result=($this->getName() == $other->getName());
        if ($result) {
            return ['result' => true, 'instantiation'=> $inst];
        }

        return ['result' => false];
    }

}
