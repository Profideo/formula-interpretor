<?php

namespace Profideo\FormulaInterpreterBundle\Tests;

use Profideo\FormulaInterpreterBundle\DependencyInjection\ProfideoFormulaInterpreterExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractProfideoFormulaInterpreterExtensionTest extends KernelTestCase
{
    /**
     * @var ProfideoFormulaInterpreterExtension
     */
    protected $extension;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp()
    {
        $this->extension = new ProfideoFormulaInterpreterExtension();

        $this->container = new ContainerBuilder();
        $this->container->registerExtension($this->extension);
    }

    abstract protected function loadConfiguration(ContainerBuilder $container, $resource);
}
