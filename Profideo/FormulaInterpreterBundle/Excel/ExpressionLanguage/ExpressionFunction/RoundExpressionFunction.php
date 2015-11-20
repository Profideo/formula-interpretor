<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction;

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

    /**
     * {@inheritdoc}
     */
    protected function getMinArguments()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaxArguments()
    {
        return 2;
    }
}
