<?php

namespace Profideo\FormulaInterpretorBundle\Tests;

use Profideo\FormulaInterpretorBundle\DependencyInjection\FormulaInterpretorExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractFormulaInterpretorExtensionTest extends KernelTestCase
{
    private $extension;
    private $container;

    protected function setUp()
    {
        $this->extension = new FormulaInterpretorExtension();

        $this->container = new ContainerBuilder();
        $this->container->registerExtension($this->extension);
    }

    abstract protected function loadConfiguration(ContainerBuilder $container, $resource);
}
