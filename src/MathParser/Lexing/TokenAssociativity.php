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
