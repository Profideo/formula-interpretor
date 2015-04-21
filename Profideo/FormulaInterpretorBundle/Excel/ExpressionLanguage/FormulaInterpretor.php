<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ParsedExpression;

ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class FormulaInterpretor
{
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function parse($expression, array $names = array())
    {
        $parsedExpression = $this->expressionLanguage->parse($expression, $names);

        foreach ($parsedExpression->getNodes()->nodes as $node) {

        }


        return $parsedExpression;
    }

    public function compile($expression, array $values = array())
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, array_keys($values));
        }

        return $this->expressionLanguage->compile($expression, array_keys($values));
    }

    public function evaluate($expression, array $values = array())
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, array_keys($values));
        }

        return $this->expressionLanguage->evaluate($expression, $values);
    }
}
