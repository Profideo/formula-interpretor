<?php

namespace Profideo\FormulaInterpretorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('formula_interpretor');

        $rootNode
            ->append($this->getFormulaInterpretorExcelNode())
        ;

        return $treeBuilder;
    }

    private function getFormulaInterpretorExcelNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('excel');

        $node
            ->append($this->getFormulaInterpretorExcelConstantsNode())
            ->append($this->getFormulaInterpretorExcelFunctionsNode())
            ->append($this->getFormulaInterpretorExcelScopeNode())
            ->end()
        ;

        return $node;
    }

    private function getFormulaInterpretorExcelConstantsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('constants');

        $node
            ->useAttributeAsKey('name')
            ->treatNullLike(array())
            ->prototype('array')
                ->children()
                    ->scalarNode('value')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('translations')
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getFormulaInterpretorExcelFunctionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('functions');

        $node
            ->useAttributeAsKey('name')
            ->treatNullLike(array())
            ->prototype('array')
                ->children()
                    ->scalarNode('class')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()

                    ->arrayNode('arguments')
                        ->children()
                            ->integerNode('min')
                                ->isRequired()
                                ->min(0)
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('max')
                                ->info('-1 means no max arguments')
                                ->isRequired()
                                ->min(-1)
                                ->defaultValue(-1)
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('translations')
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getFormulaInterpretorExcelScopeNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('scopes');

        $node
            ->useAttributeAsKey('name')
            ->treatNullLike(array())
            ->prototype('array')
                ->children()
                    ->arrayNode('constants')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('functions')
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('start_with_equal')
                        ->defaultFalse()
                    ->end()
                    ->integerNode('minimum_number_of_functions')
                        ->min(0)
                        ->defaultValue(0)
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
