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
    public $debug = false;

    public function parse(array $tokens)
    {
        try {
            $tokens = $this->filterTokens($tokens);
            $this->tokens = $tokens;

            return $this->ShuntingYard($tokens);
        } catch( \Exception $e) {
            echo "Caught exception $e\n";

            echo "Operator stack: ";
            print_r($this->operatorStack);

            echo "Operand stack:";
            var_dump($this->operandStack);

            die();
        }
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
            if ($this->debug) {
                echo "token[$index] = $token\n";
            }

            // Check for implicit multiplication
            if (Token::canFactorsInImplicitMultiplication($lastToken, $token)) {
                    // Push the current token back on the stack
                    // and instead insert a multiplcation token.
                    // Since this is added with the standard precedence,
                    // expressions such as "x^2x" will be parsed as "x^2*x".
                    $index = $index-1;
                    $token = new Token('*', TokenType::MultiplicationOperator, TokenPrecedence::BinaryMultiplication);

                    if ($this->debug) echo "Implicit multiplication detected\n";
            }

            $node = Node::factory($token);

            if ($node instanceof NumberNode || $node instanceof VariableNode || $node instanceof ConstantNode) {
                $val = $token->getValue();
                $this->operandStack->push($node);

                if ($this->debug) echo "Pushing terminal operand\n";
            } elseif ($token->getType() == TokenType::FunctionName) {
                $val = $token->getValue();
                $this->operatorStack->push($token);

                if ($this->debug) echo "Pushing FunctionNode operator\n";
            } elseif ($token->getType() == TokenType::OpenParenthesis) {
                $val = $token->getValue();
                $this->operatorStack->push($token);

                if ($this->debug) echo "Pushing OpenParanthesis operator\n";

            } elseif ($token->getType() == TokenType::CloseParenthesis) {
                $clean = false;

                if ($this->debug) echo "Handing CloseParenthesis\n";
                while($popped = $this->operatorStack->pop()) {
                    if ($this->debug) echo "  Popping and handling $popped\n";

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
                if ($this->debug) echo "Handling operator\n";
                // Unary minus and unary plus?

                $unary = false;

                if ($token->getType() == TokenType::AdditionOperator || $token->getType() == TokenType::SubtractionOperator) {

                    // Unary if it is the first token or if the previous token was '('

                    $currentOperator = $this->operatorStack->peek();

                    if ($this->debug) {
                        echo "Looking for unary operator: lastToken=$lastToken, currentOperator = $currentOperator\n";
                    }
                    if (($currentOperator->getType() == TokenType::Sentinel && $this->operandStack->count() == 0)
                        || $lastToken->getType() == TokenType::OpenParenthesis
                        || $lastToken->getType() == TokenType::UnaryMinus) {

                            if ($this->debug) echo "Unary ".$token->getValue()." detected.\n";
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

                        if ($this->debug) echo " Popping and handling $popped\n";

                        // Throw exception if $popped is not an operator
                        if ($popped->getType() == TokenType::FunctionName)
                            throw new \Exception("Misplaced function name");

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

        if ($this->debug) echo "Token list exhausted. Popping remaining operators.\n";

        while($this->operatorStack->count() > 1) {
            $token = $this->operatorStack->pop();
            if ($this->debug) echo "  Popping $token\n";
            $node = $this->handleExpression($token);
            $this->operandStack->push($node);
        }

        if ($this->operandStack->count() > 1) throw new \Exception("Syntax error (stack not empty)");

        return $this->operandStack->pop();
    }

    private function handleExpression($token)
    {
        $arity = $token->getArity();

        if ($arity == 1) {
            $left = $this->operandStack->pop();
            if ($left === null) throw new \Exception("Syntax error");

            return new ExpressionNode($left, $token->getValue());
        }
        if ($arity == 2) {
            $right = $this->operandStack->pop();
            $left = $this->operandStack->pop();
            if ($right === null || $left === null) throw new \Exception("Syntax error: $token");
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
