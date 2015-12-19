<?php namespace MathParser\Parsing;

use MathParser\Lexing\Token;
use MathParser\Lexing\TokenType;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Lexing\TokenAssociativity;

use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;

use MathParser\Parsing\Stack;

use MathParser\Lexing\Exceptions\UnexpectedTokenException;

use MathParser\Interpreting\PrettyPrinter;

class Parser
{
    /**
     * @Token[]
     */
    private $tokens;

    public function parse(array $tokens)
    {
        $tokens = $this->filterTokens($tokens);
        $this->tokens = $tokens;

        return $this->ShuntingYard($tokens);
    }

    private function ShuntingYard(array $tokens)
    {
        $Sentinel = new Token('%%END%%', -1, TokenPrecedence::Sentinel);
        $operatorStack = new Stack();
        $operatorStack->push($Sentinel);

        $operandStack = new Stack();
        $endToken = new Token('', TokenType::Terminator, -2);

        // array_push($tokens, $endToken);

        foreach($tokens as $token)
        {
            //echo "operandStack:";
            //$this->showOperandStack($operandStack);
            //echo "operatorStack:"; var_dump($operatorStack->data);

            $node = Node::factory($token);

            if ($node instanceof NumberNode || $node instanceof VariableNode || $node instanceof ConstantNode) {

                $val = $token->getValue();
                echo "Pushing terminal $val\n";

                $operandStack->push($node);
            } elseif ($token->getType() == TokenType::FunctionName) {
                $val = $token->getValue();
                echo "Pushing $val\n";

                $operatorStack->push($token);
            } elseif ($token->getType() == TokenType::OpenParenthesis) {
                $val = $token->getValue();
                echo "Pushing $val\n";

                $operatorStack->push($token);
            } elseif ($token->getType() == TokenType::CloseParenthesis) {
                $clean = false;

                while($popped = $operatorStack->pop()) {
                    if($popped->getType() == TokenType::OpenParenthesis) {
                        $clean = true;
                        break;
                    } else {
                        $right = $operandStack->pop();
                        $left = $operandStack->pop();
                        $node = new ExpressionNode($left, $popped->getValue(), $right);

                        // var_dump($node);
                        $operandStack->push($node);
                    }
                }
                if (!$clean) throw new \Exception("Parenthesis mismatch");

                $current = $operatorStack->peek();
                if ($current->getType() == TokenType::FunctionName) {
                    $tok = $operatorStack->pop();
                    $op = $operandStack->pop();
                    $node = new FunctionNode($tok->getValue(), $op);
                    $operandStack->push($node);
                }
            } else {

                // Build tree of all operators on the stack with lower precedence
                while (
                    $token->getPrecedence() < $operatorStack->peek()->getPrecedence() ||
                    ($token->getPrecedence() == $operatorStack->peek()->getPrecedence() && $token->getAssociativity() == TokenAssociativity::Right)
                ) {
                    $right = $operandStack->pop();
                    $left = $operandStack->pop();

                    if ($left === null || $right === null) {
                        // echo "operandStack:"; $this->showOperandStack($operandStack);
                        // echo "operatorStack:"; $this->showOperatorStack($operatorStack);
                        // echo "left:"; var_dump($left);
                        // echo "right:"; var_dump($right);
                        // echo "token:"; var_dump($token);
                        throw new \Exception("Syntax error");
                    }

                    $popped = $operatorStack->pop();

                    // Throw exception if $popped is not an operator
                    if ($popped->getType() == TokenType::FunctionName)
                        throw new Exception("Misplaced function name");

                    $node = new ExpressionNode($left, $popped->getValue(), $right);
                    $operandStack->push($node);

                    // echo "Forming tree\n";
                }
                $val = $token->getValue();
                echo "Pushing operator $val\n";

                $operatorStack->push($token);
            }
        }

        // Pop remaining operators

        //echo "Out of tokens\n";
        //var_dump($operatorStack->data);
        //$this->showOperandStack($operandStack);
        //echo "\n";

        while($operatorStack->count() > 1){
            $token = $operatorStack->pop();

            echo "Popping ";
            var_dump($token->getValue());

            $right = $operandStack->pop();
            $left = $operandStack->pop();

            if ($left === null || $right === null) throw new \Exception("Syntax error");

            // Throw exception if $popped is not an operator
            if ($token->getType() == TokenType::FunctionName)
                throw new \Exception("Misplaced function name");

            $node = new ExpressionNode($left, $token->getValue(), $right);

            $operandStack->push($node);
        }

        if ($operandStack->count() > 1) throw new \Exception("Syntax error (stack not empty)");

        return $operandStack->pop();
    }

    private function showOperandStack($stack)
    {
        var_dump($stack->data);
        $printer = new PrettyPrinter();

        foreach($stack->data as $tree)
        {
            var_dump($tree->accept($printer));
        }
    }


    private function filterTokens(array $tokens)
    {
        $filteredTokens = array_filter($tokens, function (Token $t) {
            return $t->getType() !== TokenType::Whitespace;
        });

        // Return the array values only, because array_filter preserves the keys
        return array_values($filteredTokens);
    }


}
