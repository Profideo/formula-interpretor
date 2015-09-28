<?php

namespace Profideo\FormulaInterpreterBundle\Tests\Excel\ExpressionLanguage\Fixtures;

use Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\ExpressionFunction;

class MondeExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s()', $this->getName());
    }

    protected function getEvaluatorFunction()
    {
        return 'LE MONDE !';
    }
}
