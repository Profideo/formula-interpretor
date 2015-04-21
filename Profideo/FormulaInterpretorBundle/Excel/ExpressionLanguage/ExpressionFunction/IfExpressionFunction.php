<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

class IfExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        list($condition, $returnIfTrue, $returnIfFalse) = array_merge(func_get_args(), array(0, var_export(false, true)));

        return sprintf('%s(%s, %s, %s)', $this->getName(), $condition, $returnIfTrue, $returnIfFalse);
    }

    protected function getEvaluatorFunction()
    {
        list(, $condition, $returnIfTrue, $returnIfFalse) = array_merge(func_get_args(), array(0, false));

        return $condition ? $returnIfTrue : $returnIfFalse;
    }
}
