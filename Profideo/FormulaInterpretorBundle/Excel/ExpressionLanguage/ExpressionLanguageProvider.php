<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var array
     */
    private $functions;

    /**
     * @param array $functions
     */
    public function __construct(array $functions = array())
    {
        $this->functions = $functions;
    }

    /**
     * @return array|\Symfony\Component\ExpressionLanguage\ExpressionFunction[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }
}