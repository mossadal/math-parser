<?php namespace MathParser\Lexing;

use MathParser\Lexing\TokenType;

final class TokenAssociativity
{
    const None = -1;
    const Left = 1;
    const Right = 2;

    public static function get($type)
    {
        if ($type == TokenType::ExponentiationOperator) return self::Left;
        return self::Right;
    }
}
