<?php
/*
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*/

/**
 * @namespace MathParser::Interpreting
 * Namepace for the AST transformers implementing the Visitor interface.
 */
namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;


use MathParser\Exceptions\UnknownFunctionException;
use MathParser\Exceptions\UnknownOperatorException;
use MathParser\Exceptions\DivisionByZeroException;

/**
 * Differentiate an abstract syntax tree (AST).
 *
 * Implementation of a Visitor, transforming an AST into another AST
 * representing the derivative of the original AST.
 *
 * The class implements differentiation rules for all arithmetic operators
 * as well as every elementary function recognized by StdMathLexer and
 * StdmathParser, handling for example the product rule and the chain
 * rule correctly.
 *
 * To keep the resulting AST reasonably simple, a number of simplification
 * rules are built in.
 *
 * ## Example:
 *
 * ~~~{.php}
 * $parser = new StdMathParser();
 * $f = $parser->parse('exp(2x)+xy');
 * $ddx = new Differentiator('x');     // Create a d/dx operator
 * $df = $f->accept($ddx);             // $df now contains the AST of '2exp(2x)+y'
 * ~~~
 *
 * TODO: handle user specified functions
 *
 */
class Differentiator implements Visitor
{
    /**
     * Variable that we differentiate with respect to
     *
     * @var string $variable
     **/
    private $variable;

    /**
     * Class constructor
     *
     * @param string $variable Differentiation variable
     */
    public function __construct($variable)
    {
        $this->variable = $variable;
    }

    /**
     * Create a Node representing 'x+y'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing 'x+y'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $x and $y are both NumberNodes, return a single NumberNode containing their sum
     * - If $x or $y are NumberNodes representing 0, return the other term unchanged
     *
     * @param Node|int $x First term
     * @param Node|int $y Second term
     * @return Node
     */
    private function createAdditionNode($x, $y)
    {
        if (is_int($x)) $x = new NumberNode($x);
        if (is_int($y)) $y = new NumberNode($y);

        if ($x instanceof NumberNode && $y instanceof NumberNode) {
            return new NumberNode($x->getValue() + $y->getValue());
        }

        if ($x instanceof NumberNode && $x->getValue() == 0) {
            return $y;
        }
        if ($y instanceof NumberNode && $y->getValue() == 0) {
            return $x;
        }

        return new ExpressionNode($x, '+', $y);
    }

    /**
     * Create a Node representing 'x-y'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing 'x-y'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $y is null, return a unary minus node '-x' instead
     * - If $x and $y are both NumberNodes, return a single NumberNode containing their difference
     * - If $y is a NumberNode representing 0, return $x unchanged
     * - If $x and $y are equal, return '0'
     *
     * @param Node|int $x Minuend
     * @param Node|int $y Subtrahend
     * @return Node
     */
    private function createSubtractionNode($x, $y)
    {
        if ($y === null) return $this->createUnaryMinusNode($x);

        if (is_int($x)) $x = new NumberNode($x);
        if (is_int($y)) $y = new NumberNode($y);

        if ($x instanceof NumberNode && $y instanceof NumberNode) {
            return new NumberNode($x->getValue() + $y->getValue());
        }

        if ($y instanceof NumberNode && $y->getValue() == 0) {
            return $x;
        }

        if (Node::compareNodes($x,$y)) {
            return new NumberNode(0);
        }

        return new ExpressionNode($x, '-', $y);
    }

    /**
     * Create a Node representing '-x'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing '-x'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $x is a NumberNodes, return a single NumberNode containing its negative
     * - If $x already is a unary minus, 'x=-y', return y
     *
     * @param Node|int $x Operand
     * @return Node
     */
    private function createUnaryMinusNode($x)
    {
        if (is_int($x)) $x = new NumberNode($x);

        if ($x instanceof NumberNode) {
            return new NumberNode(-$x->getValue());
        }

        // --x => x
        if ($x instanceof ExpressionNode && $x->getOperator() == '-' && $x->getRight() === null) {
            return $x->getLeft();
        }

        return new ExpressionNode($x, '-', null);
    }

    /**
     * Create a Node representing 'x*y'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing 'x*y'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $x and $y are both NumberNodes, return a single NumberNode containing their product
     * - If $x or $y is a NumberNode representing 0, return '0'
     * - If $x or $y is a NumberNode representing 1, return the other factor
     *
     * @param Node|int $x First factor
     * @param Node|int $y Second factor
     * @return Node
     */
    private function createMultiplicationNode($x, $y)
    {
        if (is_int($x)) $x = new NumberNode($x);
        if (is_int($y)) $y = new NumberNode($y);

        if ($x instanceof NumberNode && $y instanceof NumberNode) {
            return new NumberNode($x->getValue() * $y->getValue());
        }

        if ($x instanceof NumberNode && $x->getValue() == 1) {
            return $y;
        }
        if ($x instanceof NumberNode && $x->getValue() == 0) {
            return new NumberNode(0);
        }

        if ($y instanceof NumberNode && $y->getValue() == 1) {
            return $x;
        }
        if ($y instanceof NumberNode && $y->getValue() == 0) {
            return new NumberNode(0);
        }

        return new ExpressionNode($x, '*', $y);
    }

    /**
     * Create a Node representing 'x/y'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing 'x/y'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $x is a NumberNode representing 0, return 0
     * - If $y is a NumberNode representing 1, return $x
     * - If $x and $y are equal, return '1'
     *
     * @param Node|int $x Numerator
     * @param Node|int $y Denominator
     * @return Node
     */
    private function createDivisionNode($x, $y)
    {
        if (is_int($x)) $x = new NumberNode($x);
        if (is_int($y)) $y = new NumberNode($y);

        // Return rational number?
        // if ($x instanceof NumberNode && $y instanceof NumberNode)
        //    return new NumberNode($x->getValue() / $y->getValue());

        if ($y instanceof NumberNode && $y->getValue() == 0) {
            throw new DivisionByZeroException();
        }

        if ($y instanceof NumberNode && $y->getValue() == 1) {
            return $x;
        }
        if ($x instanceof NumberNode && $x->getValue() == 0) {
            return new NumberNode(0);
        }

        if (Node::compareNodes($x,$y)) {
            return new NumberNode(1);
        }

        return new ExpressionNode($x, '/', $y);
    }

    /**
     * Create a Node representing 'x^y'
     *
     * Using some simplification rules, create a NumberNode or ExpressionNode
     * giving an AST correctly representing 'x^y'.
     *
     * ### Simplification rules:
     *
     * - To simplify the use of the function, convert integer params to NumberNodes
     * - If $x and $y are both NumberNodes, return a single NumberNode containing x^y
     * - If $y is a NumberNode representing 0, return '1'
     * - If $y is a NumberNode representing 1, return $x
     * - If $x is already a power x=a^b and $y is a NumberNode, return a^(b*y)
     *
     * @param Node|int $x Minuend
     * @param Node|int $y Subtrahend
     * @return Node
     */
    private function createExponentiationNode($x, $y)
    {
        if (is_int($x)) $x = new NumberNode($x);
        if (is_int($y)) $y = new NumberNode($y);

        if ($y instanceof NumberNode && $y->getValue() == 0) {
            return new NumberNode(1);
        }
        if ($y instanceof NumberNode && $y->getValue() == 1) {
            return $x;
        }

        if ($x instanceof NumberNode && $y instanceof NumberNode) {
            return new NumberNode(pow($x->getValue(), $y->getValue()));
        }

        // (x^a)^b -> x^(ab) for a, b numbers
        if ($x instanceof ExpressionNode && $x->getRight() instanceof NumberNode && $y instanceof NumberNode) {
            $power = new NumberNode($x->getRight()->getValue() * $y->getValue());
            $base = $x->getLeft();
            return new ExpressionNode($base, '^', $power);
        }

        return new ExpressionNode($x, '^', $y);
    }

    /**
     * Differentiate an ExpressionNode
     *
     * Using the usual rules for differentiating, create an ExpressionNode
     * giving an AST correctly representing the derivative `(x op y)'`
     * where `op` is one of `+`, `-`, `*`, `/` or `^`
     *
     * ### Differentiation rules:
     *
     * - \\( (f+g)' = f' + g' \\)
     * - \\( (f-g) ' = f' - g' \\)
     * - \\( (-f)' = -f' \\)
     * - \\( (f*g)' = f'g + f g' \\)
     * - \\( (f/g)' = (f' g - f g')/g^2 \\)
     * - \\( (f^g)' = f^g  (g' \\log(f) + g/f) \\) with a simpler expression when g is a NumberNode
     *
     * @throws UnknownOperatorException if the operator is something other than
     *      `+`, `-`, `*`, `/` or `^`
     *
     * @param ExpressionNode $node AST to be differentiated
     * @return Node
     */
    public function visitExpressionNode(ExpressionNode $node)
    {
        $operator = $node->getOperator();

        $leftValue = $node->getLeft()->accept($this);

        if ($node->getRight()) {
            $rightValue = $node->getRight()->accept($this);
        } else {
            $rightValue = null;
        }

        // Perform the right operation based on the operator
        switch ($operator) {
            case '+':
                return $this->createAdditionNode($leftValue, $rightValue);
            case '-':
                return $this->createSubtractionNode($leftValue, $rightValue);

            // Product rule (fg)' = fg' + f'g
            case '*':
                return $this->createAdditionNode(
                    $this->createMultiplicationNode($node->getLeft(), $rightValue),
                    $this->createMultiplicationNode($leftValue, $node->getRight())
                );

            // Quotient rule (f/g)' = (f'g - fg')/g^2
            case '/':
                $term1 = $this->createMultiplicationNode($leftValue, $node->getRight());
                $term2 = $this->createMultiplicationNode($node->getLeft(), $rightValue);
                $numerator = $this->createSubtractionNode($term1, $term2);
                $denominator = $this->createExponentiationNode($node->getRight(), new NumberNode(2));
                return $this->createDivisionNode($numerator, $denominator);

            // f^g = exp(g log(f)), so (f^g)' = f^g (g'log(f) + g/f)
            case '^':
                $base = $node->getLeft();
                $exponent = $node->getRight();

                if ($exponent instanceof NumberNode) {
                    $power = $exponent->getValue();
                    $fpow = $this->createExponentiationNode($base, $power-1);
                    return $this->createMultiplicationNode($power, $this->createMultiplicationNode($fpow, $leftValue));
                } else {
                    $term1 = $this->createMultiplicationNode($rightValue, new FunctionNode('log', $node->getLeft()));
                    $term2 = $this->createDivisionNode($node->getRight(), $node->getLeft());
                    $factor2 = $this->createAdditionNode($term1, $term2);

                    return $this->createMultiplicationNode($node, $factor2);
                }

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Differentiate a NumberNode
     *
     * Create a NumberNode representing '0'. (The derivative of
     * a constant is indentically 0).
     *
     * @param NumberNode $node AST to be differentiated
     * @return Node
     */
    public function visitNumberNode(NumberNode $node)
    {
        return new NumberNode(0);
    }

    /**
     * Differentiate a VariableNode
     *
     * Create a NumberNode representing '0' or '1' depending on
     * the differetiation variable.
     *
     * @param NumberNode $node AST to be differentiated
     * @return Node
     */
    public function visitVariableNode(VariableNode $node)
    {
        if ($node->getName() == $this->variable) {
            return new NumberNode(1);
        }
        else {
            return new NumberNode(0);
        }
    }

    /**
     * Differentiate a FunctionNode
     *
     * Create an ExpressionNode giving an AST correctly representing the
     * derivative `f'` where `f` is an elementary function.
     *
     * ### Differentiation rules:
     *
     * * \\( \\sin(f(x))' = f'(x)  \\cos(f(x)) \\)
     * * \\( \\cos(f(x))' = -f'(x)  \\sin(f(x)) \\)
     * * \\( \\tan(f(x))' = f'(x) (1 + \\tan(f(x))^2 \\)
     * * \\( \\operatorname{cot}(f(x))' = f'(x) (-1 + \\operatorname{cot}(f(x))^2 \\)
     * * \\( \\arcsin(f(x))' = f'(x) / \\sqrt{1-f(x)^2} \\)
     * * \\( \\arccos(f(x))' = -f'(x) / \\sqrt{1-f(x)^2} \\)
     * * \\( \\arctan(f(x))' = f'(x) / (1+f(x)^2) \\)
     * * \\( \\operatorname{arccot}(f(x))' = -f'(x) / (1+f(x)^2) \\)
     * * \\( \\exp(f(x))' = f'(x) \\exp(f(x)) \\)
     * * \\( \\log(f(x))' = f'(x) / f(x) \\)
     * * \\( \\ln(f(x))' = f'(x) / (\\log(10) * f(x)) \\)
     * * \\( \\sqrt{f(x)}' = f'(x) / (2 \\sqrt{f(x)} \\)
     * * \\( \\sinh(f(x))' = f'(x) \\cosh(f(x)) \\)
     * * \\( \\cosh(f(x))' = f'(x) \\sinh(f(x)) \\)
     * * \\( \\tanh(f(x))' = f'(x) (1-\\tanh(f(x))^2) \\)
     * * \\( \\operatorname{coth}(f(x))' = f'(x) (1-\\operatorname{coth}(f(x))^2) \\)
     * * \\( \\operatorname{arsinh}(f(x))' = f'(x) / \\sqrt{f(x)^2+1} \\)
     * * \\( \\operatorname{arcosh}(f(x))' = f'(x) / \\sqrt{f(x)^2-1} \\)
     * * \\( \\operatorname{artanh}(f(x))' = f'(x) (1-f(x)^2) \\)
     * * \\( \\operatorname{arcoth}(f(x))' = f'(x) (1-f(x)^2) \\)
     *
     * @throws UnknownFunctionException if the function name doesn't match
     *          one of the above
     *
     * @param FunctionNode $node AST to be differentiated
     * @return Node
     */
    public function visitFunctionNode(FunctionNode $node)
    {
        $inner = $node->getOperand()->accept($this);
        $arg = $node->getOperand();

        switch ($node->getName()) {

            case 'sin':
                $df = new FunctionNode('cos', $arg);
                break;
            case 'cos':
                $sin = new FunctionNode('sin', $arg);
                $df = $this->createUnaryMinusNode($sin);
                break;
            case 'tan':
                $tansquare = $this->createExponentiationNode($node, 2);
                $df = $this->createAdditionNode(1, $tansquare);
                break;
            case 'cot':
                $cotsquare = $this->createExponentiationNode($node, 2);
                $df = $this->createAdditionNode($this->createUnaryMinusNode(1), $cotsquare);
                break;

            case 'arcsin':
                $denom = new FunctionNode('sqrt',
                    $this->createSubtractionNode(1, $this->createExponentiationNode($arg, 2)));
                return $this->createDivisionNode($inner, $denom);

            case 'arccos':
                $denom = new FunctionNode('sqrt',
                    $this->createSubtractionNode(1, $this->createExponentiationNode($arg, 2)));
                return  $this->createDivisionNode($this->createUnaryMinusNode($inner), $denom);

            case 'arctan':
                $denom = $this->createAdditionNode(1, $this->createExponentiationNode($arg, 2));
                return $this->createDivisionNode($inner, $denom);

            case 'arccot':
                $denom = $this->createAdditionNode(1, $this->createExponentiationNode($arg, 2));
                $df = $this->createUnaryMinusNode($this->createDivisionNode(1, $denom));
                break;

            case 'exp':
                $df = new FunctionNode('exp', $arg);
                break;
            case 'log':
                return $this->createDivisionNode($inner, $arg);
            case 'lg':
                $denominator = $this->createMultiplicationNode(new FunctionNode('log', new NumberNode(10)), $arg);
                return $this->createDivisionNode($inner, $denominator);

            case 'sqrt':
                $denom = $this->createMultiplicationNode(2, $node);
                return $this->createDivisionNode($inner, $denom);

            case 'sinh':
                $df = new FunctionNode('cosh', $arg);
                break;

            case 'cosh':
                $df = new FunctionNode('sinh', $arg);
                break;

            case 'tanh':
                $tanhsquare = $this->createExponentiationNode(new FunctionNode('tanh', $arg), 2);
                $df = $this->createSubtractionNode(1, $tanhsquare);
                break;

            case 'coth':
                $cothsquare = $this->createExponentiationNode(new FunctionNode('coth', $arg), 2);
                $df = $this->createSubtractionNode(1, $cothsquare);
                break;

            case 'arsinh':
                $temp = $this->createAdditionNode($this->createExponentiationNode($arg, 2), 1);
                return $this->createDivisionNode($inner, new FunctionNode('sqrt', $temp));

            case 'arcosh':
                $temp = $this->createSubtractionNode($this->createExponentiationNode($arg, 2), 1);
                return $this->createDivisionNode($inner, new FunctionNode('sqrt', $temp));

            case 'artanh':
            case 'arcoth':
                $denominator = $this->createSubtractionNode(1, $this->createExponentiationNode($arg, 2));
                return $this->createDivisionNode($inner, $denominator);

            default:
                throw new UnknownFunctionException($node->getName());
        }

        return $this->createMultiplicationNode($inner, $df);
    }

    /**
     * Differentiate a ConstantNode
     *
     * Create a NumberNode representing '0'. (The derivative of
     * a constant is indentically 0).
     *
     * @param ConstantNode $node AST to be differentiated
     * @return Node
     */
    public function visitConstantNode(ConstantNode $node)
    {
        return new NumberNode(0);
    }
}
