<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

use Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError;

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
        $args = func_get_args();

        if (! is_int($args[2])) {
            throw new ExpressionError(
                sprintf('The function %s expects integer as parameter 2 but "%s" given', $this->getName(), gettype($args[2]))
            );
        }

        return round($args[1], $args[2], PHP_ROUND_HALF_UP);
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
