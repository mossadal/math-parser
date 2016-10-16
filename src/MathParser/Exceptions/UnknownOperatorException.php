<?php
/*
 * @package     Exceptions
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

 namespace MathParser\Exceptions;

 /**
  * Exception thrown when parsing or evaluating expressions containing an
  * unknown oprator.
  *
  * This should not happen under normal circumstances.
  */
class UnknownOperatorException extends MathParserException
{
    /** @var string The unknown operator. */
    private $operator;

    /** Constructor. Create a UnknownOperatorException */
    public function __construct($operator)
    {
        parent::__construct("Unknown operator $operator.");

        $this->operator = $operator;
    }

    /**
     * Get the unkown operator that was encountered.
     *
     * @retval string
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
