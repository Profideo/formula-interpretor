<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "MAX" function that can be used in an expression.
 */
class MaxExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

    /**
     * @return float|int|null
     */
    protected function getEvaluatorFunction()
    {
        $maxValue = null;

        foreach (func_get_args() as $key => $argument) {
            if (0 !== $key) {
                $argument = is_numeric($argument) ? $argument : 0;

                if (null === $maxValue || $argument > $maxValue) {
                    $maxValue = $argument;
                }
            }
        }

        return $maxValue;
    }
}
