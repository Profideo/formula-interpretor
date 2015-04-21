<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class PowExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s, %s)', $this->getName(), func_get_arg(0), func_get_arg(1));
    }

    protected function getEvaluatorFunction()
    {
        return pow(func_get_arg(1), func_get_arg(2));
    }
}
