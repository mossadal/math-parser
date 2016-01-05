<?php
/*
* @package     Parsing
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2015 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*
*/

namespace MathParser\Parsing\Nodes\Factories;

use MathParser\Parsing\Nodes\Factories\AdditionNodeFactory;
use MathParser\Parsing\Nodes\Factories\SubtractionNodeFactory;
use MathParser\Parsing\Nodes\Factories\MultiplicationNodeFactory;
use MathParser\Parsing\Nodes\Factories\DivisionNodeFactory;
use MathParser\Parsing\Nodes\Factories\ExponentiationNodeFactory;

class ExpressionNodeFactory {
    protected $additionNodeFactory;
    protected $subtractionNodeFactory;
    protected $multiplicationNodeFactory;
    protected $divisionNodeFactory;
    protected $exponentiationNodeFactory;

    public function __construct()
    {
        $this->additionNodeFactory = new AdditionNodeFactory();
        $this->subtractionNodeFactory = new SubtractionNodeFactory();
        $this->multiplicationNodeFactory = new MultiplicationNodeFactory();
        $this->divisionNodeFactory = new DivisionNodeFactory();
        $this->exponentiationNodeFactory = new ExponentiationNodeFactory();
    }

    public function addition($leftOperand, $rightOperand)
    {
        return $this->additionNodeFactory($leftOperand, $rightOperand);
    }

    public function subtraction($leftOperand, $rightOperand)
    {
        return $this->subtractionNodeFactory($leftOperand, $rightOperand);
    }

    public function multiplication($leftOperand, $rightOperand)
    {
        return $this->multiplicationNodeFactory($leftOperand, $rightOperand);
    }

    public function division($leftOperand, $rightOperand)
    {
        return $this->divisionNodeFactory($leftOperand, $rightOperand);
    }

    public function exponentiation($leftOperand, $rightOperand)
    {
        return $this->exponentiationNodeFactory($leftOperand, $rightOperand);
    }

}
