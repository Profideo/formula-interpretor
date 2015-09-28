<?php

namespace Profideo\FormulaInterpretorBundle\Tests;

use Profideo\FormulaInterpretorBundle\DependencyInjection\ProfideoFormulaInterpretorExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractProfideoFormulaInterpretorExtensionTest extends KernelTestCase
{
    /**
     * @var ProfideoFormulaInterpretorExtension
     */
    protected $extension;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp()
    {
        $this->extension = new ProfideoFormulaInterpretorExtension();

        $this->container = new ContainerBuilder();
        $this->container->registerExtension($this->extension);
    }

    abstract protected function loadConfiguration(ContainerBuilder $container, $resource);
}
