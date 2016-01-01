<?php
/*
 * @package     Exceptions
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

 namespace MathParser\Exceptions;

class SyntaxErrorException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Syntax error.");
    }
}
