<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction;

use Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionError;
use Profideo\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

/**
 * @see Profideo\Component\ExpressionLanguage\ExpressionFunction
 *
 * Represents a function that can be used in an expression.
 *
 * A function is defined by two PHP callables. The callables are used
 * by the language to compile and/or evaluate the function.
 *
 * The "compiler" function is used at compilation time and must return a
 * PHP representation of the function call (it receives the function
 * arguments as arguments).
 *
 * The "evaluator" function is used for expression evaluation and must return
 * the value of the function call based on the values defined for the
 * expression (it receives the values as a first argument and the function
 * arguments as remaining arguments).
 */
abstract class ExpressionFunction extends BaseExpressionFunction
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(
            $name,
            function () {
                $this->validateCompilerFunctionArguments($this, func_get_args());

                return call_user_func_array([$this, 'getCompilerFunction'], func_get_args());
            },
            function () {
                $this->validateEvaluatorFunctionArguments($this, func_get_args());

                return call_user_func_array([$this, 'getEvaluatorFunction'], func_get_args());
            }
        );
    }

    /**
     * @return mixed
     */
    abstract protected function getCompilerFunction();

    /**
     * @return mixed
     */
    abstract protected function getEvaluatorFunction();

    /**
     * @return int
     */
    abstract protected function getMinArguments();

    /**
     * @return int
     */
    abstract protected function getMaxArguments();

    /**
     * @param ExpressionFunction $expressionFunction
     * @param $arguments
     */
    private function validateCompilerFunctionArguments(ExpressionFunction $expressionFunction, $arguments)
    {
        $this->validateArguments(count($arguments), $this->getMinArguments(), $this->getMaxArguments());
    }

    /**
     * @param ExpressionFunction $expressionFunction
     * @param $arguments
     */
    private function validateEvaluatorFunctionArguments(ExpressionFunction $expressionFunction, $arguments)
    {
        $this->validateArguments(count($arguments) - 1, $this->getMinArguments(), $this->getMaxArguments());
    }

    /**
     * @param $numArguments
     * @param $minArguments
     * @param $maxArguments
     */
    private function validateArguments($numArguments, $minArguments, $maxArguments)
    {
        if ($numArguments < $minArguments || (-1 !== $maxArguments && $numArguments > $maxArguments)) {
            $errorMessage = sprintf('Wrong number of arguments for %s() function: %d given', $this->getName(), $numArguments);

            if ($minArguments === $maxArguments) {
                $errorMessage .= sprintf(', %d expected', $maxArguments);
            } elseif (-1 !== $maxArguments) {
                $errorMessage .= sprintf(', between %d and %d expected', $minArguments, $maxArguments);
            } else {
                $errorMessage .= sprintf(', at least %d expected', $minArguments);
            }

            throw new ExpressionError($errorMessage);
        }
    }
}
