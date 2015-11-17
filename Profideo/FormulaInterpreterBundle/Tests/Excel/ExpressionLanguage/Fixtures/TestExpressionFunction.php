<?php

namespace Profideo\FormulaInterpreterBundle\Tests\Excel\ExpressionLanguage\Fixtures;

use Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\ExpressionFunction;

class TestExpressionFunction extends ExpressionFunction
{
    /**
     * @var ServiceTest1
     */
    private $serviceTest1;

    /**
     * @var ServiceTest2
     */
    private $serviceTest2;

    protected function getCompilerFunction()
    {
        return sprintf('%s()', $this->getName());
    }

    protected function getEvaluatorFunction()
    {
        return "{$this->serviceTest1->getTest()} {$this->serviceTest2->getTest()}";
    }

    public function setTest1(ServiceTest1 $serviceTest1)
    {
        $this->serviceTest1 = $serviceTest1;

        return $this;
    }

    public function setTest2(ServiceTest2 $serviceTest2)
    {
        $this->serviceTest2 = $serviceTest2;

        return $this;
    }
}
