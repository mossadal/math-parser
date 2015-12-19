<?php namespace MathParser\Lexing;

final class TokenPrecedence
{
    const Sentinel = -1;
    const Terminal = 0;
    const FunctionEvaluation = 1;
    const BinaryAddition = 10;
    const BinarySubtraction = 10;
    const UnaryMinus = 20;
    const BinaryMultiplication = 30;
    const BinaryDivision = 30;
    const BinaryExponentiation = 40;
    const OpenParenthesis = -1;
    const CloseParenthesis = -1;
}
