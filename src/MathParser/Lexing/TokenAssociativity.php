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

/**
 * Defining associativity of tokens.
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
 */
final class TokenAssociativity
{
    const None = -1;
    const Left = 1;
    const Right = 2;

    /**
     * Returns associativity of given token type.
     *
     * @param int $type Token type
     * @return int $associativity (left or right).
     */
    public static function get($type)
    {
        if ($type == TokenType::ExponentiationOperator) return self::Right;
        return self::Left;
    }
}
