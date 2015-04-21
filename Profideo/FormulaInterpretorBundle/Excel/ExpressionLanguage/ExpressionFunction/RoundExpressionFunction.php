<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class RoundExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s, %s)', $this->getName(), func_get_arg(0), func_get_arg(1));
    }

    protected function getEvaluatorFunction()
    {
        return round(func_get_arg(1), func_get_arg(2), PHP_ROUND_HALF_UP);
    }
}
