<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "ROUND" function that can be used in an expression.
 */
class RoundExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s, %s)', $this->getName(), func_get_arg(0), func_get_arg(1));
    }

    /**
     * @return float
     */
    protected function getEvaluatorFunction()
    {
        return round(func_get_arg(1), func_get_arg(2), PHP_ROUND_HALF_UP);
    }
}
