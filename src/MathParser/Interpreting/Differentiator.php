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

use MathParser\Parsing\Nodes\Factories\AdditionNodeFactory;
use MathParser\Parsing\Nodes\Factories\SubtractionNodeFactory;
use MathParser\Parsing\Nodes\Factories\MultiplicationNodeFactory;
use MathParser\Parsing\Nodes\Factories\DivisionNodeFactory;
use MathParser\Parsing\Nodes\Factories\ExponentiationNodeFactory;

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
    protected $variable;

    protected $additionNodeFactory;
    protected $subtractionNodeFactory;
    protected $multiplicationNodeFactory;
    protected $divisionNodeFactory;
    protected $exponentiationNodeFactory;

    /**
     * Class constructor
     *
     * @param string $variable Differentiation variable
     */
    public function __construct($variable)
    {
        $this->variable = $variable;

        $this->additionNodeFactory = new AdditionNodeFactory();
        $this->subtractionNodeFactory = new SubtractionNodeFactory();
        $this->multiplicationNodeFactory = new MultiplicationNodeFactory();
        $this->divisionNodeFactory = new DivisionNodeFactory();
        $this->exponentiationNodeFactory = new ExponentiationNodeFactory();
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
                return $this->additionNodeFactory->makeNode($leftValue, $rightValue);
            case '-':
                return $this->subtractionNodeFactory->makeNode($leftValue, $rightValue);

            // Product rule (fg)' = fg' + f'g
            case '*':
                return $this->additionNodeFactory->makeNode(
                    $this->multiplicationNodeFactory->makeNode($node->getLeft(), $rightValue),
                    $this->multiplicationNodeFactory->makeNode($leftValue, $node->getRight())
                );

            // Quotient rule (f/g)' = (f'g - fg')/g^2
            case '/':
                $term1 = $this->multiplicationNodeFactory->makeNode($leftValue, $node->getRight());
                $term2 = $this->multiplicationNodeFactory->makeNode($node->getLeft(), $rightValue);
                $numerator = $this->subtractionNodeFactory->makeNode($term1, $term2);
                $denominator = $this->exponentiationNodeFactory->makeNode($node->getRight(), new NumberNode(2));
                return $this->divisionNodeFactory->makeNode($numerator, $denominator);

            // f^g = exp(g log(f)), so (f^g)' = f^g (g'log(f) + g/f)
            case '^':
                $base = $node->getLeft();
                $exponent = $node->getRight();

                if ($exponent instanceof NumberNode) {
                    $power = $exponent->getValue();
                    $fpow = $this->exponentiationNodeFactory->makeNode($base, $power-1);
                    return $this->multiplicationNodeFactory->makeNode($power, $this->multiplicationNodeFactory->makeNode($fpow, $leftValue));
                } else {
                    $term1 = $this->multiplicationNodeFactory->makeNode($rightValue, new FunctionNode('log', $node->getLeft()));
                    $term2 = $this->divisionNodeFactory->makeNode($node->getRight(), $node->getLeft());
                    $factor2 = $this->additionNodeFactory->makeNode($term1, $term2);

                    return $this->multiplicationNodeFactory->makeNode($node, $factor2);
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
                $tansquare = $this->exponentiationNodeFactory->makeNode($node, 2);
                $df = $this->additionNodeFactory->makeNode(1, $tansquare);
                break;
            case 'cot':
                $cotsquare = $this->exponentiationNodeFactory->makeNode($node, 2);
                $df = $this->additionNodeFactory->makeNode($this->createUnaryMinusNode(1), $cotsquare);
                break;

            case 'arcsin':
                $denom = new FunctionNode('sqrt',
                    $this->subtractionNodeFactory->makeNode(1, $this->exponentiationNodeFactory->makeNode($arg, 2)));
                return $this->divisionNodeFactory->makeNode($inner, $denom);

            case 'arccos':
                $denom = new FunctionNode('sqrt',
                    $this->subtractionNodeFactory->makeNode(1, $this->exponentiationNodeFactory->makeNode($arg, 2)));
                return  $this->divisionNodeFactory->makeNode($this->createUnaryMinusNode($inner), $denom);

            case 'arctan':
                $denom = $this->additionNodeFactory->makeNode(1, $this->exponentiationNodeFactory->makeNode($arg, 2));
                return $this->divisionNodeFactory->makeNode($inner, $denom);

            case 'arccot':
                $denom = $this->additionNodeFactory->makeNode(1, $this->exponentiationNodeFactory->makeNode($arg, 2));
                $df = $this->createUnaryMinusNode($this->divisionNodeFactory->makeNode(1, $denom));
                break;

            case 'exp':
                $df = new FunctionNode('exp', $arg);
                break;
            case 'log':
                return $this->divisionNodeFactory->makeNode($inner, $arg);
            case 'lg':
                $denominator = $this->multiplicationNodeFactory->makeNode(new FunctionNode('log', new NumberNode(10)), $arg);
                return $this->divisionNodeFactory->makeNode($inner, $denominator);

            case 'sqrt':
                $denom = $this->multiplicationNodeFactory->makeNode(2, $node);
                return $this->divisionNodeFactory->makeNode($inner, $denom);

            case 'sinh':
                $df = new FunctionNode('cosh', $arg);
                break;

            case 'cosh':
                $df = new FunctionNode('sinh', $arg);
                break;

            case 'tanh':
                $tanhsquare = $this->exponentiationNodeFactory->makeNode(new FunctionNode('tanh', $arg), 2);
                $df = $this->subtractionNodeFactory->makeNode(1, $tanhsquare);
                break;

            case 'coth':
                $cothsquare = $this->exponentiationNodeFactory->makeNode(new FunctionNode('coth', $arg), 2);
                $df = $this->subtractionNodeFactory->makeNode(1, $cothsquare);
                break;

            case 'arsinh':
                $temp = $this->additionNodeFactory->makeNode($this->exponentiationNodeFactory->makeNode($arg, 2), 1);
                return $this->divisionNodeFactory->makeNode($inner, new FunctionNode('sqrt', $temp));

            case 'arcosh':
                $temp = $this->subtractionNodeFactory->makeNode($this->exponentiationNodeFactory->makeNode($arg, 2), 1);
                return $this->divisionNodeFactory->makeNode($inner, new FunctionNode('sqrt', $temp));

            case 'artanh':
            case 'arcoth':
                $denominator = $this->subtractionNodeFactory->makeNode(1, $this->exponentiationNodeFactory->makeNode($arg, 2));
                return $this->divisionNodeFactory->makeNode($inner, $denominator);

            default:
                throw new UnknownFunctionException($node->getName());
        }

        return $this->multiplicationNodeFactory->makeNode($inner, $df);
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
