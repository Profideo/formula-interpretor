<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class MaxExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

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
