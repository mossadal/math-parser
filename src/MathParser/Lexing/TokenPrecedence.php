<?php namespace MathParser\Lexing;

use MathParser\Lexing\TokenType;
use MathParser\Exceptions\UnknownTokenException;

final class TokenPrecedence
{
    const Sentinel = -1;
    const Terminal = 0;
    const FunctionEvaluation = 1;
    const BinaryAddition = 10;
    const BinarySubtraction = 10;
    const UnaryMinus = 20;
    const BinaryMultiplication = 30;
    const BinaryDivision = 30;
    const BinaryExponentiation = 40;
    const OpenParenthesis = -1;
    const CloseParenthesis = -1;

    public static function get($type)
    {
        switch($type) {
        case TokenType::PosInt:
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
