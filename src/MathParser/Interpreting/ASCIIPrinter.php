<?php
/*
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2016 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*/

namespace MathParser\Interpreting;

use MathParser\Interpreting\Visitors\Visitor;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;

use MathParser\Parsing\Nodes\IntegerNode;
use MathParser\Parsing\Nodes\RationalNode;
use MathParser\Parsing\Nodes\NumberNode;

use MathParser\Lexing\StdMathLexer;
use MathParser\Lexing\TokenAssociativity;

use MathParser\Exceptions\UnknownConstantException;

/**
 * Create LaTeX code for prettyprinting a mathematical expression
 * (for example via MathJax)
 *
 * Implementation of a Visitor, transforming an AST into a string
 * giving LaTeX code for the expression.
 *
 * The class in general does *not* generate the best possible LaTeX
 * code, and needs more work to be used in a production setting.
 *
 * ## Example:
 * ~~~{.php}
 * $parser = new StdMathParser();
 * $f = $parser->parse('exp(2x)+xy');
 * printer = new LaTeXPrinter();
 * result = $f->accept($printer);    // Generates "e^{2x}+xy"
 * ~~~
 *
 * Note that surrounding `$`, `$$` or `\begin{equation}..\end{equation}`
 * has to be added manually.
 *
 */
class ASCIIPrinter implements Visitor
{
    /** StdMathLexer $lexer */
    private $lexer;

    /** Constructor. Create an ASCIIPrinter. */
    public function __construct()
    {
        $this->lexer = new StdMathLexer();
    }

    /**
     * Generate ASCII output code for an ExpressionNode
     *
     * Create a string giving ASCII output representing an ExpressionNode `(x op y)`
     * where `op` is one of `+`, `-`, `*`, `/` or `^`
     *
     *
     * @param ExpressionNode $node AST to be typeset
     * @retval string
     */
    public function visitExpressionNode(ExpressionNode $node)
    {
        $left = $node->getLeft();
        $leftValue = $this->parenthesize($left, $node);
        $operator = $node->getOperator();

        $right = $node->getRight();

        // Unary minus
        if ($operator == '-' && $right === null)  {
            if ($left instanceof ExpressionNode) return "-($leftValue)";
        }

        if ($right) {
            $rightValue = $this->parenthesize($right, $node);

            return "$leftValue$operator$rightValue";
        }

        return "$operator$leftValue";

    }


    public function visitNumberNode(NumberNode $node)
    {
        $val = $node->getValue();
        return "$val";
    }

    public function visitIntegerNode(IntegerNode $node)
    {
        $val = $node->getValue();
        return "$val";
    }

    public function visitRationalNode(RationalNode $node)
    {
        $p = $node->getNumerator();
        $q = $node->getDenominator();
        if ($q == 1) return "$p";
        //if ($p < 1) return "($p/$q)";
        return "$p/$q";
    }

    public function visitVariableNode(VariableNode $node)
    {
        return (string)($node->getName());
    }


    public function visitFunctionNode(FunctionNode $node)
    {
        $functionName = $node->getName();

        $operand = $node->getOperand()->accept($this);

        return "$functionName($operand)";
    }

    public function visitConstantNode(ConstantNode $node)
    {
        switch($node->getName()) {
            case 'pi': return 'pi';
            case 'e': return 'e';
            case 'i': return 'i';
            case 'NAN': return 'NAN';
            case 'INF': return 'INF';

            default: throw new UnknownConstantException($node->getName());
        }
    }

    public function parenthesize(Node $node, ExpressionNode $cutoff, $prepend='')
    {
        $text = $node->accept($this);

        if ($node instanceof ExpressionNode) {
            if ($node->strictlyLowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
        }

        if (($node instanceof NumberNode || $node instanceof IntegerNode || $node instanceof RationalNode) && $node->getValue() < 0)
        {
            return "($text)";
        }

        // Treat rational numbers as divisions on printing
        if ($node instanceof RationalNode && $node->getDenominator() != 1) {
            $fakeNode = new ExpressionNode($node->getNumerator(), '/', $node->getDenominator());

            if ($fakeNode->lowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
        }

        return "$prepend$text";

    }

}
