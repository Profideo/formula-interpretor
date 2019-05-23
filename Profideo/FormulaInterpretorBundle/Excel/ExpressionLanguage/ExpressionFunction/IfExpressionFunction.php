<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "IF" function that can be used in an expression.
 */
class IfExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        list($condition, $returnIfTrue, $returnIfFalse) = array_merge(func_get_args(), array(var_export(false, true)));

        return sprintf('%s(%s, %s, %s)', $this->getName(), $condition, $returnIfTrue, $returnIfFalse);
    }

    /**
     * @return mixed
     */
    protected function getEvaluatorFunction()
    {
        list(, $condition, $returnIfTrue, $returnIfFalse) = array_merge(func_get_args(), array(false));

        return $condition ? $returnIfTrue : $returnIfFalse;
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
        return 3;
    }
}
