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
use MathParser\Exceptions\UnknownTokenException;

/**
 * Token precedence definitions.
 *
 * Tokens with higher precedence "bind harder", affecting the parsing,
 * for example, the input string "2+3*4" should be parsed as (+ 2 (* 3 4))
 * whereas the input string "2*3+4" should be parsed as (+ (* 2 3) 4).
 *
 */

final class TokenPrecedence
{
    const BinaryAddition = 10;
    const BinarySubtraction = 10;
    const BinaryMultiplication = 30;
    const BinaryDivision = 30;
    const UnaryMinus = 35;
    const BinaryExponentiation = 40;

    // These are dummy values
    const Sentinel = -1;
    const Terminal = 0;
    const OpenParenthesis = -1;
    const CloseParenthesis = -1;
    const FunctionEvaluation = 1;

    /**
     * Returns the standard precedence of a token type.
     *
     * @return int precedence
     */
    public static function get($type)
    {
        switch($type) {
        case TokenType::PosInt:
        case TokenType::Integer:
        case TokenType::RealNumber:
        case TokenType::Identifier:
        case TokenType::Constant:
            return self::Terminal;

        case TokenType::FunctionName:
            return self::FunctionEvaluation;

        case TokenType::OpenParenthesis:
            return self::OpenParenthesis;

        case TokenType::CloseParenthesis:
            return self::CloseParenthesis;

        case TokenType::UnaryMinus:
            return self::UnaryMinus;

        case TokenType::AdditionOperator:
        case TokenType::SubtractionOperator:
            return self::BinaryAddition;

        case TokenType::MultiplicationOperator:
        case TokenType::DivisionOperator:
            return self::BinaryMultiplication;

        case TokenType::ExponentiationOperator:
            return self::BinaryExponentiation;

        case TokenType::Terminator:
        case TokenType::Whitespace:
        case TokenType::Sentinel:
            return self::Sentinel;

        default:
            throw new UnknownTokenException($type);
        }
    }
}
