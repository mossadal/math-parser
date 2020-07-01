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
use MathParser\Exceptions\UnknownOperatorException;

use MathParser\Parsing\Nodes\Traits\Sanitize;

/**
 * AST node representing a binary operator
 */
class ExpressionNode extends Node
{
    use Sanitize;

    /**
     * Node $left Left operand
     */
    private $left;
    /** string $operator Operator, e.g. '+', '-', '*', '/' or '^' **/
    private $operator;
    /** Node $right Right operand **/
    private $right;

    /** int $precedence Precedence. Operators with higher prcedence bind harder **/
    private $precedence;
    /** LEFT_ASSOC | RIGHT_ASSOC $associativity Associativity of operator. **/
    private $associativity;

    /** integer constant representing left associatve operators */
    const LEFT_ASSOC = 1;
    /** integer constant representing left associatve operators */
    const RIGHT_ASSOC = 2;

    /**
     * Constructor
     *
     * Construct a binary operator node from (one or) two operands and an operator.
     *
     * For convenience, the constructor accept int or float as operands, automatically
     * converting these to NumberNodes
     *
     * ###Example
     *
     * ~~~{.php}
     * $node = new ExpressionNode(1,'+',2);
     * ~~~
     *
     * @param Node|null|int|float $left First operand
     * @param string $operator Name of operator
     * @param Node|null|int|float $right Second operand
     *
     */
    function __construct($left, $operator = null, $right = null)
    {
        $this->left = $this->sanitize($left);
        $this->operator = $operator;
        $this->right = $this->sanitize($right);

        switch($operator) {
            case '+':
                $this->precedence = 10;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '-':
                $this->precedence = 10;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '*':
                $this->precedence = 20;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '/':
                $this->precedence = 20;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '~':
                $this->precedence = 25;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '^':
                $this->precedence = 30;
                $this->associativity = self::RIGHT_ASSOC;
                break;

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Return the first (left) operand.
     *
     * @return  Node|null
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set the left operand.
     *
     * @return void
     */
    public function setLeft($operand)
    {
        $this->left = $operand;
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
     * Set the operator.
     *
     * @return void
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
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
     * Set the right operand.
     *
     * @return void
     */
    public function setRight($operand)
    {
        $this->right = $operand;
    }

    /**
     * Return the precedence of the ExpressionNode.
     *
     * @return int precedence
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * Implementing the Visitable interface.
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitExpressionNode($this);
    }

    /**
    * Returns true if the node can represent a unary operator, i.e. if
     * the operator is '+' or '-'-
     *
     * @return boolean
     */
    public function canBeUnary()
    {
        return $this->operator == '+' || $this->operator == '-' || $this->operator == '~';
    }

    /**
     * Returns true if the current Node has lower precedence than the one
     * we compare with.
     *
     * In case of a tie, we also consider the associativity.
     * (Left associative operators are lower precedence in this context.)
     *
     * @param Node $other Node to compare to.
     * @return boolean
     */
    public function lowerPrecedenceThan($other)
    {
        if (!($other instanceof ExpressionNode)) return false;

        if ($this->getPrecedence() < $other->getPrecedence()) return true;
        if ($this->getPrecedence() > $other->getPrecedence()) return false;

        if ($this->associativity == self::LEFT_ASSOC) return true;

        return false;

    }

    public function strictlyLowerPrecedenceThan($other)
    {
        if (!($other instanceof ExpressionNode)) return false;

        if ($this->getPrecedence() < $other->getPrecedence()) return true;

        return false;

    }

    /** Implementing the compareTo abstract method. */
    public function compareTo($other)
    {
        if ($other === null) {
            return false;
        }
        if (!($other instanceof ExpressionNode)) {
            return false;
        }

        if ($this->getOperator() != $other->getOperator()) return false;

        $thisLeft = $this->getLeft();
        $otherLeft = $other->getLeft();
        $thisRight = $this->getRight();
        $otherRight = $other->getRight();

        if ($thisLeft === null) {
            return $otherLeft === null && $thisRight->compareTo($otherRight);
        }

        if ($thisRight === null) {
            return $otherRight=== null && $thisLeft->compareTo($otherLeft);
        }

        return $thisLeft->compareTo($otherLeft) && $thisRight->compareTo($otherRight);
    }

    /** Implementing the hasInstance abstract method. */
    public function hasInstance($other,$inst=[])
    {
        if ($other === null) {
            return ['result' => false];
        }
        if (!($other instanceof ExpressionNode)) {
            return ['result' => false];
        }

        if ($this->getOperator() != $other->getOperator()) return ['result' => false];

        $thisLeft = $this->getLeft();
        $otherLeft = $other->getLeft();
        $thisRight = $this->getRight();
        $otherRight = $other->getRight();

        if ($thisLeft === null) {
            if ($otherLeft === null) {
                return $thisRight->hasInstance($otherRight,$inst);
            } else {
                return ['result' => false];
            }
        }

        if ($thisRight === null) {
            if ($otherRight === null) {
                return $thisLeft-> hasInstance($otherLeft,$inst);
            } else {
                return ['result' => false];
            }
        }
        $instLeft=$thisLeft->hasInstance($otherLeft,$inst);
        if (! $instLeft['result']) {
            return ['result' => false];
        } else {
            $instRight = $thisRight->hasInstance($otherRight,$instLeft['instantiation']);
            if (! $instRight['result']) {
                return ['result' => false];
            } else {
                return ['result'=> true, 'instantiation' => $instRight['instantiation']];
            }
        }
    }


}
