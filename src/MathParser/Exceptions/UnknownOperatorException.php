<?php
/*
 * @package     Exceptions
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

 namespace MathParser\Exceptions;

class UnknownOperatorException extends \Exception
{
    public function __construct($operator)
    {
        parent::__construct("Unknown operator $operator.");
    }
}
