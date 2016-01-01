<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */


 namespace MathParser\Parsing;

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
    protected $tokens;
    protected $operatorStack;
    protected $operandStack;

    public function parse(array $tokens)
    {
        $tokens = $this->filterTokens($tokens);

        if (self::allowImplicitMultipliication()) {
            $tokens = $this->parseImplicitMultiplication($tokens);
        }

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

            $node = Node::factory($token);

            if ($node instanceof NumberNode || $node instanceof VariableNode || $node instanceof ConstantNode) {
                $this->operandStack->push($node);

            } elseif ($token->getType() == TokenType::FunctionName) {
                $this->operatorStack->push($token);

            } elseif ($token->getType() == TokenType::OpenParenthesis) {
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
                                    $token = new Token('-', TokenType::UnaryMinus);
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


    protected function handleExpression($token)
    {
        $arity = $token->getArity();

        if ($arity == 1) {
            $left = $this->operandStack->pop();
            if ($left === null) {
                throw new SyntaxErrorException();
            }

            if ($token->getType() == TokenType::UnaryMinus && $left instanceof NumberNode) {
                return new NumberNode(-$left->getValue());
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


    protected function filterTokens(array $tokens)
    {
        $filteredTokens = array_filter($tokens, function (Token $t) {
            return $t->getType() !== TokenType::Whitespace;
        });

        // Return the array values only, because array_filter preserves the keys
        return array_values($filteredTokens);
    }

    protected function parseImplicitMultiplication(array $tokens)
    {
        $result = [];
        $lastToken = null;
        foreach ($tokens as $token) {
            if (Token::canFactorsInImplicitMultiplication($lastToken, $token)) {
                $result[] = new Token('*', TokenType::MultiplicationOperator, TokenPrecedence::BinaryMultiplication);
            }
            $lastToken = $token;
            $result[] = $token;
        }
        return $result;
    }

    protected static function allowImplicitMultipliication()
    {
        return true;
    }


}
