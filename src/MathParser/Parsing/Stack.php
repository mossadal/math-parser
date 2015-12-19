<?php namespace MathParser\Parsing;

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
