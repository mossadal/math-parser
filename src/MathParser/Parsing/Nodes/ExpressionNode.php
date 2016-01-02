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
 * AST node representing a binary operator
 */
class ExpressionNode extends Node
{
    private $left;
    private $operator;
    private $right;

    /**
     * Constructor
     *
     * Construct a binary operator node from (one or) two operands and an operator.
     *
     * For convenience, the constructor accept int or float as operands, automatically
     * converting these to NumberNodes
     *
     * @param Node|null|int|float $left First operand
     * @param string operator Name of operator
     * @param Node|null|int|float $right Second operand
     *
     */
    function __construct($left, $operator = null, $right = null)
    {
        if (is_int($left) || is_float($left)) $left = new NumberNode($left);
        if (is_int($right) || is_float($right)) $right = new NumberNode($right);

        $this->left = $left;

        // $operator and $right are optional in case we have
        // an expression consisting of a single NumberNode
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * Return the first (left) operand.
     *
     * @return Node|null
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Return the operator.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Return the second (right) operand.
     *
     * @return Node|null
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitExpressionNode($this);
    }
}
