<?php namespace MathParser\Interpreting\Visitors;

use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\ConstantNode;

interface Visitor
{
    function visitExpressionNode(ExpressionNode $node);
    function visitNumberNode(NumberNode $node);
    function visitVariableNode(VariableNode $node);
    function visitFunctionNode(FunctionNode $node);
    function visitConstantNode(ConstantNode $node);
}
