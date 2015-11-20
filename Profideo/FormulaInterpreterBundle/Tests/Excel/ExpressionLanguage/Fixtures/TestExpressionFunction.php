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

    /**
     * @var ServiceTest3
     */
    private $serviceTest3;

    protected function getCompilerFunction()
    {
        return sprintf('%s()', $this->getName());
    }

    protected function getEvaluatorFunction()
    {
        return "{$this->serviceTest1->getTest()} {$this->serviceTest2->getTest()} {$this->serviceTest3->getTest()}";
    }

    public function setTest13(ServiceTest1 $serviceTest1, ServiceTest3 $serviceTest3)
    {
        $this->serviceTest1 = $serviceTest1;
        $this->serviceTest3 = $serviceTest3;

        return $this;
    }

    public function setTest2(ServiceTest2 $serviceTest2)
    {
        $this->serviceTest2 = $serviceTest2;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMinArguments()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaxArguments()
    {
        return 0;
    }
}
