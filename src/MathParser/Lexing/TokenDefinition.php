<?php namespace MathParser\Lexing;

use MathParser\Lexing\TokenAssociativity;

class TokenDefinition
{
    private $pattern;
    private $value;
    private $tokenType;

    public function __construct($pattern, $tokenType, $value=null)
    {
        $this->pattern = $pattern;
        $this->value = $value;
        $this->tokenType = $tokenType;
    }

    public function match($input)
    {
        // Match the input with the regex pattern, storing any match found into the $matches variable,
        // along with the index of the string at which it was matched.
        $result = preg_match($this->pattern, $input, $matches, PREG_OFFSET_CAPTURE);

        // preg_match returns false if an error occured
        if ($result === false)
            throw new \Exception(preg_last_error());

        // 0 means no match was found
        if ($result === 0)
            return null;

        return $this->getTokenFromMatch($matches[0]);
    }

    private function getTokenFromMatch($match)
    {
        $value = $match[0];
        $offset = $match[1];

        // If we don't match at the beginning of the string, it fails.
        if ($offset !== 0)
            return null;

        if ($this->value) $value = $this->value;

        return new Token($value, $this->tokenType);
    }

}
