<?php

namespace Profideo\FormulaInterpreterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ProfideoFormulaInterpreterExtension extends Extension
{
    private $excelFunctionBaseName = 'profideo.formula_interpreter.excel.function';

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
            $this->buildFormulaInterpreterExcelDefinition($container, $config['excel']);
        }
    }

    /**
     * Builds excel node definition.
     *
     * @param ContainerBuilder $container
     * @param $config
     */
    private function buildFormulaInterpreterExcelDefinition(ContainerBuilder $container, $config)
    {
        foreach ($config['scopes'] as $scopeName => $scope) {
            $functions = $config['functions'];

            if (! $scope['use_default_functions']) {
                $functions = array_merge($this->getDefaultExcelFunctions(), $functions);
            }

            foreach ($functions as $functionName => $function) {
                if (!in_array($functionName, $scope['functions'])) {
                    unset($functions[$functionName]);
                }
            }

            if ($scope['use_default_functions']) {
                $functions = array_merge($this->getDefaultExcelFunctions(), $functions);
            }

            if (0 < count($diffs = array_diff($scope['functions'], array_keys($functions)))) {
                throw new InvalidConfigurationException(
                    sprintf("Unknown function(s) in '%s' scope : %s", $scopeName, implode(', ', $diffs))
                );
            }

            // Defines ExpressionFunction services.
            $functionDefinitions = array();
            foreach ($functions as $name => $function) {
                $functionClassParameter = sprintf('%s.%s.class', $this->excelFunctionBaseName, strtolower($name));
                $container->setParameter($functionClassParameter, $function['class']);

                foreach ($function['translations'] as $translation) {
                    $translation = strtoupper($translation);

                    $functionDefinition = new Definition();
                    $functionDefinition->setClass($container->getParameter($functionClassParameter));
                    $functionDefinition->setArguments(array($translation));
                    $functionDefinition->setPublic(false);

                    // SF >= 2.8
                    if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setShared')) {
                        $functionDefinition->setShared(false);
                    } else {
                        $functionDefinition->setScope(ContainerInterface::SCOPE_PROTOTYPE);
                    }

                    foreach ($function['services'] as $service) {
                        $parameters = array();

                        foreach ($service['parameters'] as $parameter) {
                            $parameters[] = new Reference(ltrim($parameter, '@'));
                        }

                        $functionDefinition->addMethodCall($service['method'], $parameters);
                    }

                    $functionService = sprintf('%s.%s', $this->excelFunctionBaseName, $translation);
                    $container->setDefinition($functionService, $functionDefinition);

                    $functionDefinitions[] = $container->getDefinition($functionService);
                }
            }

            // Defines ExpressionLanguageProvider service using a list of ExpressionFunction.
            $expressionLanguageProvider = new Definition();
            $expressionLanguageProvider->setClass($container->getParameter('profideo.formula_interpreter.excel.expression_language_provider.class'));
            $expressionLanguageProvider->setArguments([$functionDefinitions]);
            $expressionLanguageProvider->setPublic(false);
            $container->setDefinition("profideo.formula_interpreter.excel.expression_language_provider.$scopeName", $expressionLanguageProvider);

            $constantList = $config['constants'];

            if (! $scope['use_default_constants']) {
                $constantList = array_merge($this->getDefaultExcelConstants(), $constantList);
            }

            foreach ($constantList as $constantName => $constant) {
                if (!in_array($constantName, $scope['constants'])) {
                    unset($constantList[$constantName]);
                }
            }

            if ($scope['use_default_constants']) {
                $constantList = array_merge($this->getDefaultExcelConstants(), $constantList);
            }

            if (0 < count($diffs = array_diff($scope['constants'], array_keys($constantList)))) {
                throw new InvalidConfigurationException(
                    sprintf("Unknown constant(s) in '%s' scope : %s", $scopeName, implode(', ', $diffs))
                );
            }

            $constants = array();
            foreach ($constantList as $constant) {
                foreach ($constant['translations'] as $translation) {
                    $translation = strtoupper($translation);

                    $constants[$translation] = $constant['value'];
                }
            }

            // Defines ExpressionLanguage service using:
            // - ExpressionLanguageProvider service
            // - a constant list
            // - start with equal configuration
            // - minimum number of functions configuration
            $expressionLanguage = new Definition();
            $expressionLanguage->setClass($container->getParameter('profideo.formula_interpreter.excel.expression_language.class'));
            $expressionLanguage->setArguments(array(
                null,
                [$container->getDefinition("profideo.formula_interpreter.excel.expression_language_provider.$scopeName")],
                $constants,
                $scope['start_with_equal'],
                $scope['minimum_number_of_functions'],
            ));
            $expressionLanguage->setPublic(false);
            $container->setDefinition("profideo.formula_interpreter.excel.expression_language.$scopeName", $expressionLanguage);

            // Defines FormulaInterpreter service using:
            // - ExpressionLanguage service
            $formulaInterpreter = new Definition();
            $formulaInterpreter->setClass($container->getParameter('profideo.formula_interpreter.excel.formula_interpreter.class'));
            $formulaInterpreter->setArguments(array(
                $container->getDefinition("profideo.formula_interpreter.excel.expression_language.$scopeName")
            ));
            $formulaInterpreter->setPublic(true);
            $container->setDefinition("profideo.formula_interpreter.excel.$scopeName", $formulaInterpreter);
        }
    }

    /**
     * Returns a list of default functions.
     *
     * @return array
     */
    private function getDefaultExcelFunctions()
    {
        return array(
            'and' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\AndExpressionFunction',
                'translations' => array('AND', 'ET'),
                'services' => array(),
            ),
            'concatenate' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\ConcatenateExpressionFunction',
                'translations' => array('CONCATENATE', 'CONCATENER'),
                'services' => array(),
            ),
            'if' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\IfExpressionFunction',
                'translations' => array('IF', 'SI'),
                'services' => array(),
            ),
            'max' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\MaxExpressionFunction',
                'translations' => array('MAX'),
                'services' => array(),
            ),
            'min' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\MinExpressionFunction',
                'translations' => array('MIN'),
                'services' => array(),
            ),
            'or' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\OrExpressionFunction',
                'translations' => array('OR', 'OU'),
                'services' => array(),
            ),
            'pow' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\PowExpressionFunction',
                'translations' => array('POW', 'PUISSANCE'),
                'services' => array(),
            ),
            'round' => array(
                'class' => 'Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage\ExpressionFunction\RoundExpressionFunction',
                'translations' => array('ROUND', 'ARRONDI'),
                'services' => array(),
            ),
        );
    }

    /**
     * Returns a list of default constants.
     *
     * @return array
     */
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
