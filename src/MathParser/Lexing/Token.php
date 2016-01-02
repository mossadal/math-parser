<?php
/*
 * @package     Lexical analysis
 * @subpackage  Token handling
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

namespace MathParser\Lexing;

use MathParser\Lexing\TokenType;
use MathParser\Lexing\TokenPrecedence;
use MathParser\Lexing\TokenAssociativity;

/**
 * Token class
 *
 * Class to handle tokens, i.e. discrete pieces of the input string
 * that has specific meaning.
 *
 * Each token has a type as well as a precedence (priority) and a
 * specified associativity (left or right). Perhaps it would be
 * more natural to handle precedence and associativity at the parser
 * level, but for now it seems like the code is slightly more readable
 * this way. This may change in the future.
 */
class Token
{
    private $value;
    private $type;
    private $precedence;
    private $associativity;
    private $match;

    /**
     * Public constructor
     *
     * Create a token with a given value and type, as well
     * as an optional 'match' which is the actual character string
     * matching the token definition. Most of the time, $value
     * and $match are the same, but in order to handle token synonyms,
     * they may be different.
     *
     * With the current implementation, the precedence and associativity
     * are inferred automatically from the type.
     *
     * As an example illustrating the above, the natural logarithm can
     * be denoted ln() as well as log(). In order to standardize the
     * token list, both inputs might generate a token with value 'log' and
     * type TokenType::FunctionName, but the match parameter will be the
     * actual string matched, i.e. 'log' and 'ln', respectively, so that
     * the token knows its own length so that the rest of the input string
     * will be handled correctly.
     *
     * @param string $value Standardized value of Token
     * @param int $type Token type, as defined by the TokenType class
     * @param string $match Optional actual match in the input string
     */
    public function __construct($value, $type, $match=null)
    {
        $this->value = $value;
        $this->type = $type;
        $this->precedence = TokenPrecedence::get($type);
        $this->associativity = TokenAssociativity::get($type);
        $this->match = $match ? $match : $value;
    }

    /**
     * @property length
     * Length of the input string matching the token.
     *
     * @return int length of string matching the token.
     */
    public function length()
    {
        return strlen($this->match);
    }

    /**
     * @property getValue
     * Standarized value/name of the Token, usually the same as
     * what was matched in the the input string.
     *
     * @return string value of token
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @property getType
     * Returns the type of the token, as defined in the TokenType class.
     *
     * @return int token type (as defined by TokenType)
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @property getPrecedence
     * Returns the precedence/priority of the token, as defined by the
     * TokenPrecedence class.
     *
     * Tokens with higher precedence "bind harder", affecting the parsing,
     * for example, the input string "2+3*4" should be parsed as (+ 2 (* 3 4))
     * whereas the input string "2*3+4" should be parsed as (+ (* 2 3) 4).
     *
     * @return int precedence
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * @property getAssociativity
     * Returns the associativity (left or right) of the token.
     *
     * Token associativity is used to specify how tokens of the same precedence
     * should be parsed. Most operators are left associative. For some (addition
     * and multiplciation) it doesn't really matter, but for others (subtration
     * and division) it does. Input such as "1+2+3" and "3-2-1" should be parsed
     * as (+ (+ 1 2) 3) and (- (- 3 2) 1) respectively.
     *
     * The exponentiation operator on the other hand is right associative, and
     * the input "x^2^3" should be parsed as (^ x (^ 2 3)).
     *
     * @return int associativity, as defined by the TokenAssociativity class.
     */
    public function getAssociativity()
    {
        return $this->associativity;
    }

    /**
     * Helper function, returning the arity (i.e. the number of operands) the token takes.
     *
     * Arity is the number of operands that a token/operator takes. Some tokens
     * act as nullary, for example numbers, constants and variables.
     * Some are unary, in particular unary minus, and function applications,
     * whereas the standard arithmetic operators are binary, taking two operands.
     *
     * @return int arity of token
     */
    public function getArity()
    {
        switch($this->type) {
            case TokenType::UnaryMinus:
            case TokenType::FunctionName:
                return 1;
            case TokenType::PosInt:
            case TokenType::Integer:
            case TokenType::RealNumber:
            case TokenType::Constant:
            case TokenType::Identifier:
                return 0;
            default:
                return 2;
        }
    }

    /**
     * Helper function, returning true if the token represents a binary operator.
     *
     * @return boolean
     */
    public function isOperator()
    {
        return $this->getArity() == 2;
    }

    /**
     * Helper function, converting the Token to a printable string.
     *
     * @return string
     */
    public function __toString()
    {
        return "Token: [$this->value, $this->type]";
    }

    /**
     * Helper function, determining whether a pair of tokens
     * can form an implicit multiplication.
     *
     * Mathematical shorthand writing often leaves out explicit multiplication
     * symbols, writing "2x" instead of "2*x" or "2 \cdot x". The parser
     * accepts implicit multiplication if the first token is a nullary operator
     * or a a closing parenthesis, and the second token is a nullary operator
     * or an opening parenthesis. (Unless the first token is a a function name,
     * and the second is an opening parentheis.)
     *
     * @return boolean
     */
    public static function canFactorsInImplicitMultiplication($token1, $token2)
    {
        if ($token1 === null || $token2 === null) return false;

        $check1 = (
            $token1->type == TokenType::PosInt ||
            $token1->type == TokenType::Integer ||
            $token1->type == TokenType::RealNumber ||
            $token1->type == TokenType::Constant ||
            $token1->type == TokenType::Identifier ||
            $token1->type == TokenType::FunctionName ||
            $token1->type == TokenType::CloseParenthesis
        );

        if (!$check1) return false;

        $check2 = (
            $token2->type == TokenType::PosInt ||
            $token2->type == TokenType::Integer ||
            $token2->type == TokenType::RealNumber ||
            $token2->type == TokenType::Constant ||
            $token2->type == TokenType::Identifier ||
            $token2->type == TokenType::FunctionName ||
            $token2->type == TokenType::OpenParenthesis
        );

        if (!$check2) return false;

        if ($token1->type == TokenType::FunctionName && $token2->type == TokenType::OpenParenthesis)
            return false;

        return true;
    }
}
