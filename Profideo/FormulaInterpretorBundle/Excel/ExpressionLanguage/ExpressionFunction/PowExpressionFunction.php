<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "POW" function that can be used in an expression.
 */
class PowExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s, %s)', $this->getName(), func_get_arg(0), func_get_arg(1));
    }

    /**
     * @return number
     */
    protected function getEvaluatorFunction()
    {
        return pow(func_get_arg(1), func_get_arg(2));
    }
}
