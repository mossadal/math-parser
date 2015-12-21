<?php namespace MathParser\Lexing;

class StdMathLexer extends Lexer
{
    public function __construct()
    {
        $this->add(new TokenDefinition('/\d+/', TokenType::PosInt));

        $this->add(new TokenDefinition('/sqrt/', TokenType::FunctionName));
        
        $this->add(new TokenDefinition('/sin/', TokenType::FunctionName));
        $this->add(new TokenDefinition('/cos/', TokenType::FunctionName));
        $this->add(new TokenDefinition('/tan/', TokenType::FunctionName));
        $this->add(new TokenDefinition('/cot/', TokenType::FunctionName));

        $this->add(new TokenDefinition('/arcsin|asin/', TokenType::FunctionName, 'arcsin'));
        $this->add(new TokenDefinition('/arccos|acos/', TokenType::FunctionName, 'arccos'));
        $this->add(new TokenDefinition('/arctan|atan/', TokenType::FunctionName, 'arctan'));
        $this->add(new TokenDefinition('/arccot|acot/', TokenType::FunctionName, 'arccot'));

        $this->add(new TokenDefinition('/exp/', TokenType::FunctionName));
        $this->add(new TokenDefinition('/log|ln/', TokenType::FunctionName, 'log'));
        $this->add(new TokenDefinition('/lg/', TokenType::FunctionName));

        $this->add(new TokenDefinition('/\(/', TokenType::OpenParenthesis));
        $this->add(new TokenDefinition('/\)/', TokenType::CloseParenthesis));

        $this->add(new TokenDefinition('/\+/', TokenType::AdditionOperator));
        $this->add(new TokenDefinition('/\-/', TokenType::SubtractionOperator));
        $this->add(new TokenDefinition('/\*/', TokenType::MultiplicationOperator));
        $this->add(new TokenDefinition('/\//', TokenType::DivisionOperator));
        $this->add(new TokenDefinition('/\^/', TokenType::ExponentiationOperator));

        $this->add(new TokenDefinition('/pi/', TokenType::Constant));
        $this->add(new TokenDefinition('/e/', TokenType::Constant));

        $this->add(new TokenDefinition('/[a-zA-Z]/', TokenType::Identifier));

        $this->add(new TokenDefinition('/\n/', TokenType::Terminator));
        $this->add(new TokenDefinition('/\s+/', TokenType::Whitespace));

    }
}
