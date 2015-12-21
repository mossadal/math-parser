<?php namespace MathParser\Lexing;

final class TokenType
{
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

    const FunctionName = 200;

    const Constant = 300;

    const Terminator = 998;
    const Whitespace = 999;

    const Sentinel = 1000;
}
