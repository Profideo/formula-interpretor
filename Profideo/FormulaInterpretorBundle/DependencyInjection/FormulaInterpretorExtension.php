<?php

namespace Profideo\FormulaInterpretorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class FormulaInterpretorExtension extends Extension
{
    private $excelFunctionBaseName = 'profideo.formula_interpretor.excel.function';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['excel']) && !empty($config['excel'])) {
            $this->buildFormulaInterpretorExcelDefinition($container, $config['excel']);
        }
    }

    private function buildFormulaInterpretorExcelDefinition(ContainerBuilder $container, $config)
    {
        $functions = array_merge($this->getDefaultExcelFunctions(), $config['functions']);

        $functionDefinitions = array();
        foreach ($functions as $name => $function) {
            $functionClassParameter = sprintf('%s.%s.class', $this->excelFunctionBaseName, strtolower($name));
            $container->setParameter($functionClassParameter, $function['class']);

            foreach ($function['translations'] as $translation) {
                $translation = strtoupper($translation);

                $functionDefinition = new Definition();
                $functionDefinition->setClass($container->getParameter($functionClassParameter));
                $functionDefinition->setArguments(array(
                    $translation,
                    null,
                    null,
                    $function['arguments']['min'],
                    $function['arguments']['max'],
                ));
                $functionDefinition->setPublic(false);

                $functionService = sprintf('%s.%s', $this->excelFunctionBaseName, $translation);
                $container->setDefinition($functionService, $functionDefinition);

                $functionDefinitions[] = $container->getDefinition($functionService);
            }
        }

        $expressionLanguageProvider = new Definition();
        $expressionLanguageProvider->setClass($container->getParameter('profideo.formula_interpretor.excel.expression_language_provider.class'));
        $expressionLanguageProvider->setArguments([$functionDefinitions]);
        $expressionLanguageProvider->setPublic(false);
        $container->setDefinition('profideo.formula_interpretor.excel.expression_language_provider', $expressionLanguageProvider);

        $constantList = array_merge($this->getDefaultExcelConstants(), $config['constants']);
        $constants = array();
        foreach ($constantList as $constant) {
            foreach ($constant['translations'] as $translation) {
                $translation = strtoupper($translation);

                $constants[$translation] = $constant['value'];
            }
        }

        $expressionLanguage = new Definition();
        $expressionLanguage->setClass($container->getParameter('profideo.formula_interpretor.excel.expression_language.class'));
        $expressionLanguage->setArguments(array(
            null,
            [$container->getDefinition('profideo.formula_interpretor.excel.expression_language_provider')],
            $constants,
            $config['start_with_equal'],
            $config['minimum_number_of_functions'],
        ));
        $expressionLanguage->setPublic(false);
        $container->setDefinition('profideo.formula_interpretor.excel.expression_language', $expressionLanguage);
    }

    private function getDefaultExcelFunctions()
    {
        return array(
            'and' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\AndExpressionFunction',
                'arguments' => array(
                    'min' => 1,
                    'max' => -1,
                ),
                'translations' => array('AND', 'ET'),
            ),
            'concatenate' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\ConcatenateExpressionFunction',
                'arguments' => array(
                    'min' => 1,
                    'max' => -1,
                ),
                'translations' => array('CONCATENATE', 'CONCATENER'),
            ),
            'if' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\IfExpressionFunction',
                'arguments' => array(
                    'min' => 2,
                    'max' => 3,
                ),
                'translations' => array('IF', 'SI'),
            ),
            'max' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\MaxExpressionFunction',
                'arguments' => array(
                    'min' => 1,
                    'max' => -1,
                ),
                'translations' => array('MAX'),
            ),
            'min' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\MinExpressionFunction',
                'arguments' => array(
                    'min' => 1,
                    'max' => -1,
                ),
                'translations' => array('MIN'),
            ),
            'or' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\OrExpressionFunction',
                'arguments' => array(
                    'min' => 1,
                    'max' => -1,
                ),
                'translations' => array('OR', 'OU'),
            ),
            'pow' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\PowExpressionFunction',
                'arguments' => array(
                    'min' => 2,
                    'max' => 2,
                ),
                'translations' => array('POW', 'PUISSANCE'),
            ),
            'round' => array(
                'class' => 'Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionFunction\RoundExpressionFunction',
                'arguments' => array(
                    'min' => 2,
                    'max' => 2,
                ),
                'translations' => array('ROUND', 'ARRONDI'),
            ),
        );
    }

    private function getDefaultExcelConstants()
    {
        return array(
            'true' => array(
                'value' => true,
                'translations' => array('TRUE', 'VRAI'),
            ),
            'false' => array(
                'value' => false,
                'translations' => array('FALSE', 'FAUX'),
            ),
        );
    }
}
