<?php
/*
 * @package     Exceptions
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */


class UnknownNodeException extends \Exception
{
    public function __construct($node)
    {
        parent::__construct("Unknown node: $node");
    }
}
