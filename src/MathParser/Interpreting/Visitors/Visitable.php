<?php namespace MathParser\Interpreting\Visitors;

interface Visitable
{
    function accept(Visitor $visitor);
}
