<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */


/**
 * @namespace MathParser::Parsing
 *
 * Parser related classes
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

/**
 * Mathematical expression parser, based on the shunting yard algorithm.
 *
 * Parse a token string into an abstract syntax tree (AST).
 *
 * As the parser loops over the individual tokens, two stacks are kept
 * up to date. One stack ($operatorStack) consists of hitherto unhandled
 * tokens corresponding to ''operators'' (unary and binary operators, function
 * applications and parenthesis) and a stack of parsed sub-expressions (the
 * $operandStack).
 *
 * If the current token is a terminal token (number, variable or constant),
 * a corresponding node is pushed onto the operandStack.
 *
 * Otherwise, the precedence of the current token is compared to the top
 * element(t) on the operatorStack, and as long as the current token has
 * lower precedence, we keep popping operators from the stack to constuct
 * more complicated subexpressions together with the top items on the operandStack.
 *
 * Once the token list is empty, we pop the remaining operators as above, and
 * if the formula was well-formed, the only thing remaining on the operandStack
 * is a completely parsed AST, which we return.
 */
class Parser
{
    /**
     * @var Token[]
     */
    protected $tokens;
    /**
     * @var Token[]
     */
    protected $operatorStack;
    /**
     * @var Node[]
     */
    protected $operandStack;

    /**
     * Parse list of tokens
     *
     * @param Token[] $tokens
     * @return Node
     */
    public function parse(array $tokens)
    {
        // Filter away any whitespace
        $tokens = $this->filterTokens($tokens);

        // Insert missing implicit multiplication tokens
        if (self::allowImplicitMultiplication()) {
            $tokens = $this->parseImplicitMultiplication($tokens);
        }

        $this->tokens = $tokens;

        // Perform the actual parsing
        return $this->ShuntingYard($tokens);
    }

    /**
     * Implementation of the shunting yard parsing algorithm
     *
     * @param Token[] $tokens
     * @return Node
     * @throws SyntaxErrorException
     * @throws ParenthesisMismatchException
     */
    private function ShuntingYard(array $tokens)
    {
        // Push a sentinel token onto the operatorStack.
        $Sentinel = new Token('%%END%%', TokenType::Sentinel, TokenPrecedence::Sentinel);
        $this->operatorStack = new Stack();
        $this->operatorStack->push($Sentinel);

        // Clear the operandStack.
        $this->operandStack = new Stack();

        // Remember the last token handled, this is done to recognize unary operators.
        $lastToken = $Sentinel;

        // Loop over the tokens
        for ($index = 0; $index < count($tokens); $index++)
        {
            $token = $tokens[$index];

            // Push terminal tokens on the operandStack
            if ($token->getPrecedence() == TokenPrecedence::Terminal) {

                $node = Node::factory($token);
                $this->operandStack->push($node);

            // Push function applications or open parentheses `(` onto the operatorStack
            } elseif ($token->getType() == TokenType::FunctionName) {
                $this->operatorStack->push($token);

            } elseif ($token->getType() == TokenType::OpenParenthesis) {
                $this->operatorStack->push($token);

            // Handle closing parentheses
            } elseif ($token->getType() == TokenType::CloseParenthesis) {
                // Flag, checking for mismatching parentheses
                $clean = false;

                // Pop operators off the operatorStack until its empty, or
                // we find an opening parenthesis, building subexpressions
                // on the operandStack as we go.
                while ($popped = $this->operatorStack->pop()) {
                    // operatorStack ran out, the formula is not well-formed
                    if ($popped->getType() == TokenType::Sentinel) {
                        break;
                    }

                    // ok, we have our matching opening parenthesis
                    if($popped->getType() == TokenType::OpenParenthesis) {
                        $clean = true;
                        break;

                    } else {

                        $node = $this->handleExpression($popped);
                        $this->operandStack->push($node);
                    }
                }

                // Throw an error if the parenthesis couldn't be matched
                if (!$clean) {
                    throw new ParenthesisMismatchException();
                }

                // Check to see if the parenthesis pair was in fact part
                // of a function application. If so, create the corresponding
                // FunctionNode and push it onto the operandStack.
                $current = $this->operatorStack->peek();
                if ($current->getType() == TokenType::FunctionName) {
                    $tok = $this->operatorStack->pop();
                    $op = $this->operandStack->pop();
                    $node = new FunctionNode($tok->getValue(), $op);
                    $this->operandStack->push($node);
                }

            // Handle the remaining operators.
            } else {

                // Check for unary minus and unary plus.
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
                                // Unary -, replace the token.
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
                        ($token->getPrecedence() == $this->operatorStack->peek()->getPrecedence() && $token->getAssociativity() == TokenAssociativity::Left)
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


    /**
     * Create an appropriate Node from the corresponding token.
     *
     * @param Token $token
     * @return Node
     * @throws SyntaxErrorException
     */
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

    /**
     * Remove Whitespace from the token list.
     *
     * @param Token[] $tokens Input list of tokens
     * @return Token[]
     */
    protected function filterTokens(array $tokens)
    {
        $filteredTokens = array_filter($tokens, function (Token $t) {
            return $t->getType() !== TokenType::Whitespace;
        });

        // Return the array values only, because array_filter preserves the keys
        return array_values($filteredTokens);
    }

    /**
     * Insert multiplication tokens where needed (taking care of implicit mulitplication).
     *
     * @param Token[] $tokens Input list of tokens
     * @return Token[]
     */
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

    /**
     * Determine if the parser allows implicit multiplication. Create a
     * subclass of Parser, overriding this function, returning false instead
     * to diallow implicit multiplication.
     *
     * ### Example:
     *
     * ~~~{.php}
     * class ParserWithoutImplictMultiplication extends Parser {
     *   protected function allowImplicitMultiplication() {
     *     return false;
     *   }
     * }
     *
     * $lexer = new StdMathLexer();
     * $tokens = $lexer->tokenize('2x');
     * $parser = new ParserWithoutImplicitMultiplication();
     * $node = $parser->parse($tokens); // Throws a SyntaxErrorException
     * ~~~
     * @property allowImplicitMultiplication
     * @return boolean
     */
    protected static function allowImplicitMultiplication()
    {
        return true;
    }


}
