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

class ExpressionNode extends Node
{
    private $left;
    private $operator;
    private $right;
    private $precedence;

    function __construct($left, $operator = null, $right = null)
    {
        if (is_int($left)) $left = new NumberNode($left);
        if (is_int($right)) $right = new NumberNode($right);
        
        $this->left = $left;

        // $operator and $right are optional in case we have
        // an expression consisting of a single NumberNode
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * @return NumberNode
     */
    public function getLeft()
    {
        return $this->left;
    }

    public function setLeft($node)
    {
        $this->left = $node;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return NumberNode
     */
    public function getRight()
    {
        return $this->right;
    }

    public function setRight()
    {
        $this->right = $right;
    }

    public function getPrecedence()
    {
        return $this->precedence;
    }

    public function accept(Visitor $visitor)
    {
        return $visitor->visitExpressionNode($this);
    }
}
