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
    private $operatorStack;
    private $operandStack;

    public function parse(array $tokens)
    {
        $tokens = $this->filterTokens($tokens);
        $this->tokens = $tokens;

        try {
            return $this->ShuntingYard($tokens);
        } catch (\Exception $e) {
            echo "Exception caught! $e\n";

            echo "Operands ";
            $this->showOperandStack();

            echo "Operators ";
            print_r($this->operatorStack);
            die();
        }
    }

    private function ShuntingYard(array $tokens)
    {
        $Sentinel = new Token('%%END%%', TokenType::Sentinel, TokenPrecedence::Sentinel);
        $this->operatorStack = new Stack();
        $this->operatorStack->push($Sentinel);

        $this->operandStack = new Stack();

        foreach($tokens as $token)
        {
            echo "$token\n";

            $node = Node::factory($token);

            if ($node instanceof NumberNode || $node instanceof VariableNode || $node instanceof ConstantNode) {
                $val = $token->getValue();
                $this->operandStack->push($node);
            } elseif ($token->getType() == TokenType::FunctionName) {
                $val = $token->getValue();
                $this->operatorStack->push($token);
            } elseif ($token->getType() == TokenType::OpenParenthesis) {
                $val = $token->getValue();
                $this->operatorStack->push($token);
            } elseif ($token->getType() == TokenType::CloseParenthesis) {
                $clean = false;

                while($popped = $this->operatorStack->pop()) {
                    if($popped->getType() == TokenType::OpenParenthesis) {
                        $clean = true;
                        break;
                    } else {

                        $node = $this->handleExpression($popped);

                        $this->operandStack->push($node);
                    }
                }
                if (!$clean) throw new \Exception("Parenthesis mismatch");

                $current = $this->operatorStack->peek();
                if ($current->getType() == TokenType::FunctionName) {
                    $tok = $this->operatorStack->pop();
                    $op = $this->operandStack->pop();
                    $node = new FunctionNode($tok->getValue(), $op);
                    $this->operandStack->push($node);
                }
            } else {
                // Unary minus and unary plus?

                $unary = false;

                if ($token->getType() == TokenType::AdditionOperator || $token->getType() == TokenType::SubtractionOperator) {

                    // Unary if it is the first token or if the previous token was '('

                    $currentOperator = $this->operatorStack->peek();
                    if (($currentOperator->getType() == TokenType::Sentinel && $this->operandStack->count() == 0)
                        || $currentOperator->getType() == TokenType::OpenParenthesis
                        || $currentOperator->getType() == TokenType::UnaryMinus) {

                            $unary = true;

                            switch($token->getType()) {
                                // Unary +, just ignore it
                                case TokenType::AdditionOperator:
                                    $token = null;
                                    break;
                                case TokenType::SubtractionOperator:
                                    $token->setType(TokenType::UnaryMinus);
                                    break;
                            }
                    }

                }

                // Build tree of all operators on the stack with lower precedence
                if (!$unary) {
                    while (
                        $token->getPrecedence() < $this->operatorStack->peek()->getPrecedence() ||
                        ($token->getPrecedence() == $this->operatorStack->peek()->getPrecedence() && $token->getAssociativity() == TokenAssociativity::Right)
                    ) {

                        $popped = $this->operatorStack->pop();
                        echo "Popping $popped off the operator stack\n";

                        // Throw exception if $popped is not an operator
                        if ($popped->getType() == TokenType::FunctionName)
                            throw new \Exception("Misplaced function name");

                        $node = $this->handleExpression($popped);
                        $this->operandStack->push($node);
                    }
                }

                if ($token) $this->operatorStack->push($token);

            }
        }

        // Pop remaining operators

        echo "Handling dangling data:\n";
        $this->showOperandStack();
        print_r($this->operatorStack);

        while($this->operatorStack->count() > 1) {

            $token = $this->operatorStack->pop();
            $node = $this->handleExpression($token);
            $this->operandStack->push($node);
        }

        if ($this->operandStack->count() > 1) throw new \Exception("Syntax error (stack not empty)");

        return $this->operandStack->pop();
    }

    private function handleExpression($token)
    {
        echo "handleExpression($token)\n";

        $arity = $token->getArity();

        if ($arity == 1) {
            $left = $this->operandStack->pop();
            if ($left === null) throw new \Exception("Syntax error");

            return new ExpressionNode($right, $token->getValue());
        }
        if ($arity == 2) {
            $right = $this->operandStack->pop();
            $left = $this->operandStack->pop();
            if ($right === null || $left === null) throw new \Exception("Syntax error");
            return new ExpressionNode($left, $token->getValue(), $right);
        }

        throw new \Exception("Unexpected operator (incorrect arity)");
    }


    private function showOperandStack()
    {
        var_dump($this->operandStack->data);
        $printer = new PrettyPrinter();

        foreach($this->operandStack->data as $tree)
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
