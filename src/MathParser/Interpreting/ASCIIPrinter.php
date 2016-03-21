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

        if ($right) {
            $rightValue = $this->parenthesize($right, $node);

            switch($operator) {
                case '^':
                    return $leftValue.'^'.$this->bracesNeeded($right);
                default:
                    return "$leftValue$operator$rightValue";
            }

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
            case 'pi': return '\pi';
            case 'e': return 'e';
            default: throw new UnknownConstantException($node->getName());
        }
    }

    public function parenthesize(Node $node, ExpressionNode $cutoff, $prepend='')
    {
        $text = $node->accept($this);

        if ($node instanceof ExpressionNode) {

            if ($node->lowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
        }

        return "$prepend$text";

    }

    public function bracesNeeded(Node $node)
    {
        if ($node instanceof VariableNode || $node instanceof ConstantNode) {
            return $node->accept($this);
        } elseif ($node instanceof IntegerNode && $node->getValue() >= 0 && $node->getValue() <= 9) {
            return $node->accept($this);
        } else {
            return '('.$node->accept($this).')';
        }
    }
}
