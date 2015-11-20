<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction;

use Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionError;

/**
 * Represents the "OR" function that can be used in an expression.
 */
class OrExpressionFunction extends ExpressionFunction
{
    /**
     * @return string
     */
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

    /**
     * @return bool
     */
    protected function getEvaluatorFunction()
    {
        $returnValue = false;

        foreach (func_get_args() as $key => $argument) {
            if (0 !== $key) {
                $value = false;

                if (is_bool($argument)) {
                    $value = $argument;
                } elseif (is_numeric($argument)) {
                    $value = $argument !== 0;
                } elseif (is_string($argument)) {
                    $tmpArgument = strtoupper($argument);

                    if ('TRUE' === $tmpArgument) {
                        $value = true;
                    } elseif ('FALSE' === $tmpArgument) {
                        $value = false;
                    } else {
                        throw new ExpressionError(
                            sprintf('The function "%s" expects boolean values. But "%s" is text type and can not be forced to be boolean', $this->getName(), $argument)
                        );
                    }
                }

                $returnValue = $returnValue || $value;
            }
        }

        return $returnValue;
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
