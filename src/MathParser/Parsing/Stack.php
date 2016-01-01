<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */


namespace MathParser\Parsing;


class Stack {
    public $data = array();

    public function push($element) {
        $this->data[] = $element;
    }

    public function peek() {
        return end($this->data);
    }

    public function pop() {
        return array_pop($this->data);
    }

    public function count() {
        return count($this->data);
    }

}
