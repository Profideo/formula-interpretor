<?php

namespace Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage\Fixtures;

use Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\ExpressionFunction;

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
