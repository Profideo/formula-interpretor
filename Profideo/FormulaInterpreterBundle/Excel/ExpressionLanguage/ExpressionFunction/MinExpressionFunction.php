<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "MIN" function that can be used in an expression.
 */
class MinExpressionFunction extends ExpressionFunction
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

    /**
     * {@inheritdoc}
     */
    protected function getMinArguments()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaxArguments()
    {
        return -1;
    }
}
