<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

class FormulaInterpretor
{
    private $expressionLanguage;

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * @param $expression
     * @param array $names
     *
     * @return ParsedExpression
     */
    public function parse($expression, array $names = array())
    {
        return $this->expressionLanguage->parse($expression, $names);
    }

    /**
     * @param $expression
     * @param array $values
     *
     * @return string
     */
    public function compile($expression, array $values = array())
    {
        $names = array_keys($values);

        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, $names);
        }

        return $this->expressionLanguage->compile($expression, $names);
    }

    /**
     * @param $expression
     * @param array $values
     *
     * @return string
     */
    public function evaluate($expression, array $values = array())
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, array_keys($values));
        }

        return $this->expressionLanguage->evaluate($expression, $values);
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return $this->expressionLanguage->getFunctions();
    }
}
