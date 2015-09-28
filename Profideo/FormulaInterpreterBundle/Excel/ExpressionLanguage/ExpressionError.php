<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage;

class ExpressionError extends \LogicException
{
    public function __construct($message)
    {
        parent::__construct(sprintf('%s.', $message));
    }
}
