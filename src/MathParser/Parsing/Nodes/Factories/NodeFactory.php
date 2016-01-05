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

class NodeFactory {
    protected $additionFactory;
    protected $subtractionFactory;
    protected $multiplicationFactory;
    protected $divisionFactory;
    protected $exponentiationFactory;

    public function __construct()
    {
        $this->additionFactory = new AdditionNodeFactory();
        $this->subtractionFactory = new SubtractionNodeFactory();
        $this->multiplicationFactory = new MultiplicationNodeFactory();
        $this->divisionFactory = new DivisionNodeFactory();
        $this->exponentiationFactory = new ExponentiationNodeFactory();
    }

    public function addition($leftOperand, $rightOperand)
    {
        return $this->additionFactory->makeNode($leftOperand, $rightOperand);
    }

    public function subtraction($leftOperand, $rightOperand)
    {
        return $this->subtractionFactory->makeNode($leftOperand, $rightOperand);
    }

    public function multiplication($leftOperand, $rightOperand)
    {
        return $this->multiplicationFactory->makeNode($leftOperand, $rightOperand);
    }

    public function division($leftOperand, $rightOperand)
    {
        return $this->divisionFactory->makeNode($leftOperand, $rightOperand);
    }

    public function exponentiation($leftOperand, $rightOperand)
    {
        return $this->exponentiationFactory->makeNode($leftOperand, $rightOperand);
    }

}
