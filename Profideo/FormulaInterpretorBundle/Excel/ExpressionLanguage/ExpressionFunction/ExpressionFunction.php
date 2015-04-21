<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction;

use Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

abstract class ExpressionFunction extends BaseExpressionFunction
{
    /**
     * @var int
     */
    protected $minArguments;

    /**
     * @var int
     */
    protected $maxArguments;

    /**
     * @param string   $name
     * @param callable $compiler
     * @param callable $evaluator
     * @param int      $minArguments
     * @param int      $maxArguments
     */
    public function __construct($name, $compiler, $evaluator, $minArguments = 0, $maxArguments = -1)
    {
        $this->minArguments = $minArguments;
        $this->maxArguments = $maxArguments;

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
     * @param ExpressionFunction $expressionFunction
     * @param $arguments
     */
    private function validateCompilerFunctionArguments(ExpressionFunction $expressionFunction, $arguments)
    {
        $this->validateArguments(count($arguments), $this->minArguments, $this->maxArguments);
    }

    /**
     * @param ExpressionFunction $expressionFunction
     * @param $arguments
     */
    private function validateEvaluatorFunctionArguments(ExpressionFunction $expressionFunction, $arguments)
    {
        $this->validateArguments(count($arguments) - 1, $this->minArguments, $this->maxArguments);
    }

    /**
     * @param $numArguments
     * @param $minArguments
     * @param $maxArguments
     */
    private function validateArguments($numArguments, $minArguments, $maxArguments)
    {
        if ($numArguments < $minArguments || (-1 !== $maxArguments && $numArguments > $maxArguments)) {
            $errorMessage = sprintf('Nombre d\'arguments incorrects pour la fonction "%s"() : %d donné(s)', $this->getName(), $numArguments);

            if ($minArguments === $maxArguments) {
                $errorMessage .= sprintf(', %d attendu(s)', $maxArguments);
            } elseif (-1 !== $maxArguments) {
                $errorMessage .= sprintf(', entre %d et %d attendu(s)', $minArguments, $maxArguments);
            } else {
                $errorMessage .= sprintf(', au moins %d attendu(s)', $minArguments);
            }

            throw new ExpressionError($errorMessage);
        }
    }
}
