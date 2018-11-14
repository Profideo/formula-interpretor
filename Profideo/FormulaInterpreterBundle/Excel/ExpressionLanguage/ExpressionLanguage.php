<?php

namespace Profideo\FormulaInterpreterBundle\Excel\ExpressionLanguage;

use Profideo\Component\ExpressionLanguage\Expression;
use Profideo\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Profideo\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Profideo\Component\ExpressionLanguage\ParsedExpression;
use Profideo\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

class ExpressionLanguage extends BaseExpressionLanguage
{
    const CALCULATION_REGEXP_FUNCTION = '@?([A-Z][A-Z0-9\.]*)[\s]*\(';

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

    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new ExcelParser($this->functions);
        }

        return $this->parser;
    }

    protected function getLexer()
    {
        if (null === $this->lexer) {
            $this->lexer = new ExcelLexer();
        }

        return $this->lexer;
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

        return $expression;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array_keys($this->functions);
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
