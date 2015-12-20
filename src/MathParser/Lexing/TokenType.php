<?php namespace MathParser\Lexing;

final class TokenType
{
    const Number = -1;
    const Operator = -1;

    const PosInt = 1;
    const Identifier = 2;
    const OpenParenthesis = 3;
    const CloseParenthesis = 4;

    const UnaryMinus = 99;
    const AdditionOperator = 100;
    const SubtractionOperator = 101;
    const MultiplicationOperator = 102;
    const DivisionOperator = 103;
    const ExponentiationOperator = 104;

    const FunctionName = 199;
    const SIN = 200;
    const COS = 201;
    const TAN = 202;
    const COT = 203;
    const ARCSIN = 204;
    const ARCCOS = 205;
    const ARCTAN = 206;
    const ARCCOT = 207;
    const EXP = 208;
    const LOG = 209;
    const LOG10 = 210;
    const SQRT = 211;

    const Constant = 300;
    const PI = 314;
    const E = 278;

    const Terminator = 998;
    const Whitespace = 999;

    const Sentinel = 1000;
}
