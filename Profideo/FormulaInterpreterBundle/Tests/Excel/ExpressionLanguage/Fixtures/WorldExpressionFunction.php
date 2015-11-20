<?php

namespace Profideo\FormulaInterpreterBundle\Tests\Excel\ExpressionLanguage\Fixtures;

use Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\ExpressionFunction;

class WorldExpressionFunction extends ExpressionFunction
{
    protected function getCompilerFunction()
    {
        return sprintf('%s()', $this->getName());
    }

    protected function getEvaluatorFunction()
    {
        return 'WORLD !';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMinArguments()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaxArguments()
    {
        return 0;
    }
}
