<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction;

/**
 * Represents the "CONCATENATE" function that can be used in an expression.
 */
class ConcatenateExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

    /**
     * @return string
     */
    protected function getEvaluatorFunction()
    {
        $values = array();

        foreach (func_get_args() as $key => $argument) {
            if (0 !== $key) {
                if (is_bool($argument)) {
                    $argument = strtoupper(var_export($argument, true));
                }

                $values[] = $argument;
            }
        }

        return implode('', $values);
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
