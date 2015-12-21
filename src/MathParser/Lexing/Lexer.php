<?php namespace MathParser\Lexing;

class Lexer
{
    private $tokenDefinitions = [];

    public function add(TokenDefinition $tokenDefinition)
    {
        $this->tokenDefinitions[] = $tokenDefinition;
    }

    public function tokenize($input)
    {
        // The list of tokens we'll eventually return
        $tokens = [];

        // The currentIndex indicates where we are inside the input string
        $currentIndex = 0;

        while ($currentIndex < strlen($input)) {

            // We try to match only what is after the currentIndex,
            // as the content before is already converted to tokens
            $token = $this->findMatchingToken(substr($input, $currentIndex));

            // If no tokens were matched, it means that the string has invalid tokens
            // for which we did not define a token definition
            if (!$token)
                throw new Exceptions\UnknownTokenException(0,$currentIndex);

            // Add the matched token to our list of token
            $tokens[] = $token;

            // Increment the string index by the lenght of the matched token,
            // so we can now process the rest of the string.
            $currentIndex += $token->length();
        }

        return $tokens;
    }

    private function findMatchingToken($input)
    {
        // Check with all tokenDefinitions
        foreach ($this->tokenDefinitions as $tokenDefinition) {
            $token = $tokenDefinition->match($input);

            // Return the first token that was matched.
            if ($token)
                return $token;
        }

        // Return null if no tokens were matched.
        return null;
    }
}
