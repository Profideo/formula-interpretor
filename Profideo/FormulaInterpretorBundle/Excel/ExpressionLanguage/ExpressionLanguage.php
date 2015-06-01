<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

class ExpressionLanguage extends BaseExpressionLanguage
{
    const CALCULATION_REGEXP_FUNCTION = '@?([A-Z][A-Z0-9\.]*)[\s]*\(';
    const CALCULATION_REGEXP_NOT_TEXT = '/[\w.-]+(?![^"]*"(?:(?:[^"]*"){2})*[^"]*$)/';
    const CALCULATION_REGEXP_SEMICOLON_NOT_IN_TEXT = '/(;)(?=(?:[^"]|"[^"]*")*$)/';
    const CALCULATION_REGEXP_SINGLE_EQUAL_SIGN = '/[^=<>]=(?!=)/';
    const CALCULATION_REGEXP_NOT_EQUAL_SIGN = '/(?<!=)<>(?!=)/';

    /**
     * @var array
     */
    private $constants;

    /**
     * @var bool
     */
    private $startWithEqual;

    /**
     * @var int
     */
    private $minimumNumberOfFunctions;

    /**
     * @param ParserCacheInterface                  $cache
     * @param ExpressionFunctionProviderInterface[] $providers
     * @param array                                 $constants
     * @param bool                                  $startWithEqual
     * @param int                                   $minimumNumberOfFunctions
     */
    public function __construct(
        ParserCacheInterface $cache = null,
        array $providers = array(),
        array $constants = array(),
        $startWithEqual = false,
        $minimumNumberOfFunctions = 0
    ) {
        $this->constants = array_change_key_case($constants, CASE_UPPER);
        $this->startWithEqual = $startWithEqual;
        $this->minimumNumberOfFunctions = $minimumNumberOfFunctions;

        parent::__construct($cache, $providers);
    }

    /**
     * Parses an expression.
     *
     * @param Expression|string $expression The expression to parse
     * @param array             $names      An array of valid names
     *
     * @return ParsedExpression A ParsedExpression instance
     */
    public function parse($expression, $names)
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = parent::parse($this->prepare($expression), $this->getUppercaseNames($names));
        }

        return $expression;
    }

    /**
     * Compiles an expression source code.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $names      An array of valid names
     *
     * @return string The compiled PHP source code
     */
    public function compile($expression, $names = array())
    {
        return parent::compile($expression, $this->getUppercaseNames($names));
    }

    /**
     * Evaluate an expression.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $values     An array of values
     *
     * @return string The result of the evaluation of the expression
     */
    public function evaluate($expression, $values = array())
    {
        return parent::evaluate($expression, $this->getUppercaseValues($values));
    }

    /**
     * Changes the expression to be usable by the parser and strtolower it.
     * It also validates the "startWithEqual" and "minimumNumberOfFunctions" configuration.
     *
     * @param $expression
     *
     * @return string
     */
    private function prepare($expression)
    {
        $expression = trim($expression);

        // strtoupper everything except text between quotes.
        $expression = preg_replace_callback(static::CALCULATION_REGEXP_NOT_TEXT, function ($match) {
            return strtoupper($match[0]);
        }, $expression);

        // Replaces semicolons ";" by commas "," except when it's between quotes.
        $expression = preg_replace_callback(static::CALCULATION_REGEXP_SEMICOLON_NOT_IN_TEXT, function () {
            return ',';
        }, $expression);

        if ($this->startWithEqual && '=' !== substr($expression, 0, 1)) {
            throw new ExpressionError('An expression must start with an equal sign');
        }

        // Removes the first equal sign if it exists.
        $expression = ltrim($expression, '=');

        if ($this->minimumNumberOfFunctions > 0) {
            preg_match_all('/('.static::CALCULATION_REGEXP_FUNCTION.')/si', $expression, $functions);

            if (count($functions[1]) < $this->minimumNumberOfFunctions) {
                throw new ExpressionError(
                    sprintf('An expression must contains at least %d function(s)', $this->minimumNumberOfFunctions)
                );
            }
        }

        // Replace all singles equal signs by doubles equal signs
        $expression = preg_replace_callback(static::CALCULATION_REGEXP_SINGLE_EQUAL_SIGN, function ($match) {
            return str_replace('=', '==', $match[0]);
        }, $expression);

        // Replace all <> by !=
        $expression = preg_replace_callback(static::CALCULATION_REGEXP_NOT_EQUAL_SIGN, function () {
            return '!=';
        }, $expression);

        return $expression;
    }

    /**
     * @param array $names
     *
     * @return array
     */
    private function getUppercaseNames($names)
    {
        return array_merge(
            array_keys($this->constants),
            array_map('strtoupper', $names)
        );
    }

    /**
     * @param array $values
     *
     * @return array
     */
    private function getUppercaseValues($values)
    {
        return array_merge(
            $this->constants,
            array_change_key_case($values, CASE_UPPER)
        );
    }
}
