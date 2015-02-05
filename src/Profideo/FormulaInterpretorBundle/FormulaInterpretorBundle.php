<?php
namespace Profideo\FormulaInterpretorBundle;

use Profideo\FormulaInterpretorBundle\Command\FormulainterpretorChangeAttributeVisibilityCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FormulaInterpretorBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        // Register command
        $application->add(new FormulainterpretorChangeAttributeVisibilityCommand());
    }
}
