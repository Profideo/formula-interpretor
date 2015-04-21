<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class MinExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

    protected function getEvaluatorFunction()
    {
        $minValue = null;

        foreach (func_get_args() as $key => $argument) {
            if (0 !== $key) {
                $argument = is_numeric($argument) ? $argument : 0;

                if (null === $minValue || $argument < $minValue) {
                    $minValue = $argument;
                }
            }
        }

        return $minValue;
    }
}
