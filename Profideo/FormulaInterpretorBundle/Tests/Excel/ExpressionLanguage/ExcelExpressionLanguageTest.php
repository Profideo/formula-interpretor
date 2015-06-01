<?php

namespace Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage;

use Profideo\FormulaInterpretorBundle\Tests\AbstractFormulaInterpretorExtensionTest;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ExcelExpressionLanguageTest extends AbstractFormulaInterpretorExtensionTest
{
    protected function loadConfiguration(ContainerBuilder $container, $resource)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/Yaml/'));
        $loader->load($resource.'.yml');
    }

    public function testDefaultConfiguration()
    {
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        // = not required + 0 as minimum function requirement.
        $this->assertFalse($formulaInterpretor->evaluate('15>20'));
    }

    public function testTypes()
    {
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        // Tests types such as string VS numeric / int VS float / ...
        $this->assertFalse($formulaInterpretor->evaluate('"TEST">20'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST">=20'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"<20'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"<=20'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"=0'));
        $this->assertTrue($formulaInterpretor->evaluate('"TEST"<>0'));
        $this->assertTrue($formulaInterpretor->evaluate('"TEST"<>1'));
        $this->assertTrue($formulaInterpretor->evaluate('"TEST"<>"TEST2"'));
        $this->assertTrue($formulaInterpretor->evaluate('"TEST"="TEST"'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"<>"TEST"'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST">"TEST"'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST">="TEST"'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"<"TEST"'));
        $this->assertFalse($formulaInterpretor->evaluate('"TEST"<="TEST"'));

        $this->assertFalse($formulaInterpretor->evaluate('1>1.5'));
        $this->assertFalse($formulaInterpretor->evaluate('1>=1.5'));
        $this->assertTrue($formulaInterpretor->evaluate('1<1.5'));
        $this->assertTrue($formulaInterpretor->evaluate('1<=1.5'));
        $this->assertFalse($formulaInterpretor->evaluate('1=1.5'));
        $this->assertTrue($formulaInterpretor->evaluate('1<>1.5'));
        $this->assertTrue($formulaInterpretor->evaluate('1=1.0'));
        $this->assertFalse($formulaInterpretor->evaluate('1<>1.0'));
        $this->assertTrue($formulaInterpretor->evaluate('1=1'));
        $this->assertFalse($formulaInterpretor->evaluate('1=2'));
        $this->assertFalse($formulaInterpretor->evaluate('1<>1'));
        $this->assertTrue($formulaInterpretor->evaluate('1<>2'));
        $this->assertTrue($formulaInterpretor->evaluate('1.5=1.5'));
        $this->assertFalse($formulaInterpretor->evaluate('1.5<>1.5'));
    }

    /**
     * @expectedException \Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError
     * @expectedExceptionMessage An expression must start with an equal sign.
     */
    public function testEqualSignRequired()
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        $formulaInterpretor->evaluate('15>20');
    }

    /**
     * @expectedException \Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError
     * @expectedExceptionMessage An expression must contains at least 1 function(s).
     */
    public function testMinimumFunctionNumber()
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        $formulaInterpretor->evaluate('=15>20');
    }

    public function defaultConstantsDataProvider()
    {
        return array(
            array(
                'name' => 'tRUe',
                'value' => true,
            ),
            array(
                'name' => 'VraI',
                'value' => true,
            ),
            array(
                'name' => 'fAuX',
                'value' => false,
            ),
            array(
                'name' => 'FaLsE',
                'value' => false,
            ),
            array(
                'name' => 'TEST',
                'value' => null,
                'exception' => 'Variable "TEST" is not valid around position 1',
            ),
        );
    }

    /**
     * @dataProvider defaultConstantsDataProvider
     *
     * @param $name
     * @param $value
     * @param null $exception
     */
    public function testDefaultConstants($name, $value, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-0');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Symfony\Component\ExpressionLanguage\SyntaxError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($name);

        if (!$exception) {
            $this->assertSame($result, $value);
        }
    }

    public function andExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=eT()',
                'result' => null,
                'exception' => 'Wrong number of arguments for ET() function: 0 given, at least 1 expected.',
            ),
            array(
                'expression' => '=aNd(TRUE)',
                'result' => true,
            ),
            array(
                'expression' => '=aNd(TRUE;FALSE)',
                'result' => false,
            ),
            array(
                'expression' => '=aNd(5)',
                'result' => true,
            ),
            array(
                'expression' => '=aNd(0)',
                'result' => false,
            ),
            array(
                'expression' => '=aNd("TRUE")',
                'result' => true,
            ),
            array(
                'expression' => '=aNd("FALSE")',
                'result' => false,
            ),
            array(
                'expression' => '=aNd("TEST")',
                'result' => null,
                'exception' => 'The function "AND" expects boolean values. But "TEST" is text type and can not be forced to be boolean.',
            ),
        );
    }

    /**
     * @dataProvider andExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testAndExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function concatenateExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=CoNcAtEnAtE()',
                'result' => null,
                'exception' => 'Wrong number of arguments for CONCATENATE() function: 0 given, at least 1 expected.',
            ),
            array(
                'expression' => '=concatenate("Hello")',
                'result' => 'Hello',
            ),
            array(
                'expression' => '=concatenate("Hello";" ";"World")',
                'result' => 'Hello World',
            ),
            array(
                'expression' => '=concatenate("Hello";" ";2015;" ";"World")',
                'result' => 'Hello 2015 World',
            ),
            array(
                'expression' => '=concatenate("Hello";" ";"TRUE";" ";"World")',
                'result' => 'Hello TRUE World',
            ),
            array(
                'expression' => '=concatenate("Hello";" ";vrai;" ";"World")',
                'result' => 'Hello TRUE World',
            ),
            array(
                'expression' => '=concatenate("Hello";" ";faux;" ";"World")',
                'result' => 'Hello FALSE World',
            ),
        );
    }

    /**
     * @dataProvider concatenateExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testConcatenateExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function ifExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=sI()',
                'result' => null,
                'exception' => 'Wrong number of arguments for SI() function: 0 given, between 2 and 3 expected.',
            ),
            array(
                'expression' => '=iF(TRUE)',
                'result' => null,
                'exception' => 'Wrong number of arguments for IF() function: 1 given, between 2 and 3 expected.',
            ),
            array(
                'expression' => '=iF(TRUE;TRUE)',
                'result' => true,
            ),
            array(
                'expression' => '=IF(FAUX;TRUE)',
                'result' => false,
            ),
            array(
                'expression' => '=IF(TRUE;"YES";"NO")',
                'result' => 'YES',
            ),
            array(
                'expression' => '=IF(FALSE;"YES";"NO")',
                'result' => 'NO',
            ),
        );
    }

    /**
     * @dataProvider ifExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testIfExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function maxExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=mAx()',
                'result' => null,
                'exception' => 'Wrong number of arguments for MAX() function: 0 given, at least 1 expected.',
            ),
            array(
                'expression' => '=mAx(2, 3, 1, 6, -9, 7)',
                'result' => 7,
            ),
            array(
                'expression' => '=max(5, "hello")',
                'result' => 5,
            ),
            array(
                'expression' => '=max("hello", -1)',
                'result' => 0,
            ),
            array(
                'expression' => '=MAX(FAUX, 10)',
                'result' => 10,
            ),
        );
    }

    /**
     * @dataProvider maxExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testMaxExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function minExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=mIn()',
                'result' => null,
                'exception' => 'Wrong number of arguments for MIN() function: 0 given, at least 1 expected.',
            ),
            array(
                'expression' => '=mIn(2, 3, 1, 6, -9, 7)',
                'result' => -9,
            ),
            array(
                'expression' => '=min(5, "hello")',
                'result' => 0,
            ),
            array(
                'expression' => '=min("hello", -1)',
                'result' => -1,
            ),
            array(
                'expression' => '=MIN(FAUX, 10)',
                'result' => 0,
            ),
        );
    }

    /**
     * @dataProvider minExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testMinExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function orExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=oU()',
                'result' => null,
                'exception' => 'Wrong number of arguments for OU() function: 0 given, at least 1 expected.',
            ),
            array(
                'expression' => '=oR(TRUE)',
                'result' => true,
            ),
            array(
                'expression' => '=oR(TRUE;FALSE)',
                'result' => true,
            ),
            array(
                'expression' => '=oR(5)',
                'result' => true,
            ),
            array(
                'expression' => '=oR(0)',
                'result' => false,
            ),
            array(
                'expression' => '=oR("TRUE")',
                'result' => true,
            ),
            array(
                'expression' => '=oR("FALSE")',
                'result' => false,
            ),
            array(
                'expression' => '=oR("TEST")',
                'result' => null,
                'exception' => 'The function "OR" expects boolean values. But "TEST" is text type and can not be forced to be boolean.',
            ),
        );
    }

    /**
     * @dataProvider orExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testOrExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function powExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=pUiSsAnCe()',
                'result' => null,
                'exception' => 'Wrong number of arguments for PUISSANCE() function: 0 given, 2 expected.',
            ),
            array(
                'expression' => '=pOw(2)',
                'result' => null,
                'exception' => 'Wrong number of arguments for POW() function: 1 given, 2 expected.',
            ),
            array(
                'expression' => '=pOw("a", 2)',
                'result' => 0,
            ),
            array(
                'expression' => '=pUiSsAnCe(2, 8)',
                'result' => 256,
            ),
            array(
                'expression' => '=POW(-1, 20)',
                'result' => 1,
            ),
            array(
                'expression' => '=puissance(0, 0)',
                'result' => 1,
            ),
        );
    }

    /**
     * @dataProvider powExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testPowExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function roundExpressionFunctionDataProvider()
    {
        return array(
            array(
                'expression' => '=aRrOnDi()',
                'result' => null,
                'exception' => 'Wrong number of arguments for ARRONDI() function: 0 given, 2 expected.',
            ),
            array(
                'expression' => '=rOuNd(3.4)',
                'result' => null,
                'exception' => 'Wrong number of arguments for ROUND() function: 1 given, 2 expected.',
            ),
            array(
                'expression' => '=rOuNd("a", 0)',
                'result' => 0.0,
            ),
            array(
                'expression' => '=rOuNd(3.4, 0)',
                'result' => 3.0,
            ),
            array(
                'expression' => '=ARRONDI(3.5, 0)',
                'result' => 4.0,
            ),
            array(
                'expression' => '=aRrOnDi(3.6, 0)',
                'result' => 4.0,
            ),
            array(
                'expression' => '=ROUND(1.95583, 2)',
                'result' => 1.96,
            ),
            array(
                'expression' => '=ROUND(1241757, -3)',
                'result' => 1242000.0,
            ),
            array(
                'expression' => '=ROUND(5.045, 2)',
                'result' => 5.05,
            ),
            array(
                'expression' => '=ROUND(5.055, 2)',
                'result' => 5.06,
            ),
        );
    }

    /**
     * @dataProvider roundExpressionFunctionDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param null $exception
     */
    public function testRoundExpressionFunction($expression, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function testCustomFunctionsAndConstants()
    {
        $this->loadConfiguration($this->container, 'config-2');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.formula_interpretor');

        $this->assertSame('HELLO WORLD !', $formulaInterpretor->evaluate('CONCATENATE(HELLO;" ";WORLD())'));
    }
}
