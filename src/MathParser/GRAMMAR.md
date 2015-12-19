# Introduction

MathParser implements a grammar that can parse complex mathematical
expressions, including functions, variables and implicit multiplication

# Grammar

sign            =>  +|-
posint          =>  {0-9}
whitespace      =>  \s+
terminator       =>  \n

OP_ADD          => [whitespace] '+' | '-' [whitespace]
OP_MUL          => [whitespace] '*' | '/' [whitespace]
OP_POW          => [whitespace] '^' [whitespace]


integer         =>  [sign] integer | posint
atom            =>  posint | variable

exponent        =>  atom [ '^' atom ]

factor          =>  exponent | factor OP_MUL exponent

term            => factor [OP_ADD factor]

root            => term {terminator term}



a-b-c

   -
  / \
  -  c
 / \
 a  b
