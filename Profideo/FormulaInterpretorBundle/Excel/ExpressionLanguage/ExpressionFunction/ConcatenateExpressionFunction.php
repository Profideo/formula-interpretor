<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class ConcatenateExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

    protected function getEvaluatorFunction()
    {
        $values = array();

        foreach (func_get_args() as $key => $argument) {
            if (0 !== $key) {
                $values[] = $argument;
            }
        }

        return implode('', $values);
    }
}
