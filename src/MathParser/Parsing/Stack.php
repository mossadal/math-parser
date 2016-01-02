<?php
/*
 * @package     Parsing
 * @author      Frank Wikström <frank@mossadal.se>
 * @copyright   2015 Frank Wikström
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */


namespace MathParser\Parsing;

/**
 * Utility class, implementing a simple FIFO stack
 */
class Stack {
    public $data = array();

    /**
     * Push an element onto the stack.
     * @param mixed $element
     */
    public function push($element) {
        $this->data[] = $element;
    }

    /**
     * Return the top element (without popping it)
     * @return mixed
     */
    public function peek() {
        return end($this->data);
    }

    /**
     * Return the top element and remove it from the stack.
     * @return mixed
     */
    public function pop() {
        return array_pop($this->data);
    }

    /**
     * Return the current number of elements in the stack.
     * @return int
     */
    public function count() {
        return count($this->data);
    }

}
