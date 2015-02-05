<?php
namespace Profideo\FormulaInterpretor;

use Profideo\FormulaInterpretor\Command\ChangeAttributeVisibilityCommand;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FormulaInterpretorBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        // Register command
        $application->add(new ChangeAttributeVisibilityCommand());
    }
}
