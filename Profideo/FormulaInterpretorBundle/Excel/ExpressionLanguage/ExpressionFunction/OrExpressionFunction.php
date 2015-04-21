<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

use Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError;

class OrExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s(%s)', $this->getName(), implode(', ', func_get_args()));
    }

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
                        throw new ExpressionError(sprintf('La fonction "%s" attend des valeurs du type booléen. Mais "%s" est du type texte et ne peut pas être forcé pour être booléen', $this->getName(), $argument));
                    }
                }

                $returnValue = $returnValue || $value;
            }
        }

        return $returnValue;
    }
}
