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

use MathParser\Exceptions\SyntaxErrorException;
use MathParser\Exceptions\ParenthesisMismatchException;

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

        return $this->ShuntingYard($tokens);
    }

    private function ShuntingYard(array $tokens)
    {
        $Sentinel = new Token('%%END%%', TokenType::Sentinel, TokenPrecedence::Sentinel);
        $this->operatorStack = new Stack();
        $this->operatorStack->push($Sentinel);

        $this->operandStack = new Stack();

        $lastToken = $Sentinel;

        for ($index = 0; $index < count($tokens); $index++)
        {
            $token = $tokens[$index];

            // Check for implicit multiplication
            if (Token::canFactorsInImplicitMultiplication($lastToken, $token)) {
                    // Push the current token back on the stack
                    // and instead insert a multiplcation token.
                    // Since this is added with the standard precedence,
                    // expressions such as "x^2x" will be parsed as "x^2*x".
                    $index = $index-1;
                    $token = new Token('*', TokenType::MultiplicationOperator, TokenPrecedence::BinaryMultiplication);
            }

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
                    if ($popped->getType() == TokenType::Sentinel) {
                        break;
                    }

                    if($popped->getType() == TokenType::OpenParenthesis) {
                        $clean = true;
                        break;

                    } else {

                        $node = $this->handleExpression($popped);
                        $this->operandStack->push($node);
                    }
                }
                if (!$clean) {
                    throw new ParenthesisMismatchException();
                }

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
                        || $lastToken->getType() == TokenType::OpenParenthesis
                        || $lastToken->getType() == TokenType::UnaryMinus) {

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


                        $node = $this->handleExpression($popped);
                        $this->operandStack->push($node);
                    }
                }

                if ($token) $this->operatorStack->push($token);
            }

            // Remember the current token (if it hasn't been nulled, for example being a unary +)
            if ($token) $lastToken = $token;
        }


        // Pop remaining operators

        while($this->operatorStack->count() > 1) {
            $token = $this->operatorStack->pop();
            $node = $this->handleExpression($token);
            $this->operandStack->push($node);
        }

        // Stack should be empty here
        if ($this->operandStack->count() > 1) {
            throw new SyntaxErrorException();
        }

        return $this->operandStack->pop();
    }

    private function handleExpression($token)
    {
        $arity = $token->getArity();

        if ($arity == 1) {
            $left = $this->operandStack->pop();
            if ($left === null) {
                throw new SyntaxErrorException();
            }

            return new ExpressionNode($left, $token->getValue());
        }
        if ($arity == 2) {
            $right = $this->operandStack->pop();
            $left = $this->operandStack->pop();
            if ($right === null || $left === null) {
                throw new SyntaxErrorException();
            }

            return new ExpressionNode($left, $token->getValue(), $right);
        }

        throw new SyntaxErrorException();

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
