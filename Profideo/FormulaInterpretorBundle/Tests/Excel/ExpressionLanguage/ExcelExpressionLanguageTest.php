<?php

namespace Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage;

use Profideo\FormulaInterpretorBundle\Tests\AbstractProfideoFormulaInterpretorExtensionTest;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ExcelExpressionLanguageTest extends AbstractProfideoFormulaInterpretorExtensionTest
{
    protected function loadConfiguration(ContainerBuilder $container, $resource)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/Yaml/'));
        $loader->load($resource.'.yml');
    }

    public function replacesDataProvider()
    {
        return array(
            array(
                'expression' => '=IF(TRUE;"TE;ST")',
                'result' => 'IF(TRUE;"TE;ST")',
            ),
            array(
                'expression' => '=IF(1=2;"TE=ST")',
                'result' => 'IF(1==2;"TE=ST")',
            ),
            array(
                'expression' => '=IF(1<>2;"TE<>ST")',
                'result' => 'IF(1!=2;"TE<>ST")',
            ),
            array(
                'expression' => '=IF(1!=2;"TE!=ST")',
                'result' => 'IF(1!=2;"TE!=ST")',
            ),
            array(
                'expression' => '=IF(1>=2;"TE>=ST")',
                'result' => 'IF(1>=2;"TE>=ST")',
            ),
            array(
                'expression' => '=IF(1<=2;"TE<=ST")',
                'result' => 'IF(1<=2;"TE<=ST")',
            ),
            array(
                'expression' => '=IF(1<=2,"TE<=ST")',
                'result' => false,
                'exception' => 'Unexpected character "," around position 7.',
            ),
            array(
                'expression' => '=IF(1<=2;"TE<=ST","TEST")',
                'result' => false,
                'exception' => 'Unexpected character "," around position 16.',
            ),
        );
    }

    /**
     * @dataProvider replacesDataProvider
     *
     * @param $expression
     * @param $result
     */
    public function testReplaces($expression, $result, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\Component\ExpressionLanguage\SyntaxError',
                $exception
            );
        }

        $parsedExpression = $formulaInterpretor->parse($expression)->__toString();

        if (!$exception) {
            $this->assertSame($result, $parsedExpression);
        }
    }

    public function typesDataProvider()
    {
        return array(
            array(
                'expression' => '"TEST">20',
                'result' => false,
            ),
            array(
                'expression' => '"TEST">=20',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"<20',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"<=20',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"=0',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"<>0',
                'result' => true,
            ),
            array(
                'expression' => '"TEST"<>1',
                'result' => true,
            ),
            array(
                'expression' => '"TEST"<>"TEST2"',
                'result' => true,
            ),
            array(
                'expression' => '"TEST"="TEST"',
                'result' => true,
            ),
            array(
                'expression' => '"TEST"<>"TEST"',
                'result' => false,
            ),
            array(
                'expression' => '"TEST">"TEST"',
                'result' => false,
            ),
            array(
                'expression' => '"TEST">="TEST"',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"<"TEST"',
                'result' => false,
            ),
            array(
                'expression' => '"TEST"<="TEST"',
                'result' => false,
            ),
            array(
                'expression' => '1>1.5',
                'result' => false,
            ),
            array(
                'expression' => '1>=1.5',
                'result' => false,
            ),
            array(
                'expression' => '1<1.5',
                'result' => true,
            ),
            array(
                'expression' => '1<=1.5',
                'result' => true,
            ),
            array(
                'expression' => '1=1.5',
                'result' => false,
            ),
            array(
                'expression' => '1<>1.5',
                'result' => true,
            ),
            array(
                'expression' => '1=1.0',
                'result' => true,
            ),
            array(
                'expression' => '1<>1.0',
                'result' => false,
            ),
            array(
                'expression' => '1=1',
                'result' => true,
            ),
            array(
                'expression' => '1=2',
                'result' => false,
            ),
            array(
                'expression' => '1<>1',
                'result' => false,
            ),
            array(
                'expression' => '1<>2',
                'result' => true,
            ),
            array(
                'expression' => '1.5=1.5',
                'result' => true,
            ),
            array(
                'expression' => '1.5<>1.5',
                'result' => false,
            ),
        );
    }

    /**
     * @dataProvider typesDataProvider
     *
     * @param $expression
     * @param $result
     */
    public function testTypes($expression, $result)
    {
        $this->loadConfiguration($this->container, 'config-0');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test0');

        // Tests types such as string VS numeric / int VS float / ...
        if ($result) {
            $this->assertTrue($formulaInterpretor->evaluate($expression));
        } else {
            $this->assertFalse($formulaInterpretor->evaluate($expression));
        }
    }

    /**
     * @expectedException \Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage\ExpressionError
     * @expectedExceptionMessage An expression must start with an equal sign.
     */
    public function testEqualSignRequired()
    {
        $this->loadConfiguration($this->container, 'config-1');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test0');

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\Component\ExpressionLanguage\SyntaxError',
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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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
                'expression' => '=mAx(2; 3; 1; 6; -9; 7)',
                'result' => 7,
            ),
            array(
                'expression' => '=max(5; "hello")',
                'result' => 5,
            ),
            array(
                'expression' => '=max("hello"; -1)',
                'result' => 0,
            ),
            array(
                'expression' => '=MAX(FAUX; 10)',
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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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
                'expression' => '=mIn(2; 3; 1; 6; -9; 7)',
                'result' => -9,
            ),
            array(
                'expression' => '=min(5; "hello")',
                'result' => 0,
            ),
            array(
                'expression' => '=min("hello"; -1)',
                'result' => -1,
            ),
            array(
                'expression' => '=MIN(FAUX; 10)',
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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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
                'expression' => '=pOw("a"; 2)',
                'result' => 0,
            ),
            array(
                'expression' => '=pUiSsAnCe(2; 8)',
                'result' => 256,
            ),
            array(
                'expression' => '=POW(-1; 20)',
                'result' => 1,
            ),
            array(
                'expression' => '=puissance(0; 0)',
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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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
                'expression' => '=rOuNd("a"; 0)',
                'result' => 0.0,
            ),
            array(
                'expression' => '=rOuNd(3.4; 0)',
                'result' => 3.0,
            ),
            array(
                'expression' => '=ARRONDI(3.5; 0)',
                'result' => 4.0,
            ),
            array(
                'expression' => '=aRrOnDi(3.6; 0)',
                'result' => 4.0,
            ),
            array(
                'expression' => '=ROUND(1.95583; 2)',
                'result' => 1.96,
            ),
            array(
                'expression' => '=ROUND(1241757; -3)',
                'result' => 1242000.0,
            ),
            array(
                'expression' => '=ROUND(5.045; 2)',
                'result' => 5.05,
            ),
            array(
                'expression' => '=ROUND(5.055; 2)',
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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test1');

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

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test2');

        $this->assertSame('HELLO WORLD !', $formulaInterpretor->evaluate('CONCATENATE(HELLO;" ";WORLD())'));
    }

    public function getFunctions()
    {
        return array(
            array(
                'config' => 'config-0',
                'functions' => array('constant', 'AND', 'ET', 'CONCATENATE', 'CONCATENER', 'IF', 'SI', 'MAX', 'MIN', 'OR', 'OU', 'POW', 'PUISSANCE', 'ROUND', 'ARRONDI'),
                'service' => 'test0',
            ),
            array(
                'config' => 'config-1',
                'functions' => array('constant', 'AND', 'ET', 'CONCATENATE', 'CONCATENER', 'IF', 'SI', 'MAX', 'MIN', 'OR', 'OU', 'POW', 'PUISSANCE', 'ROUND', 'ARRONDI'),
                'service' => 'test1',
            ),
            array(
                'config' => 'config-2',
                'functions' => array('constant', 'AND', 'ET', 'CONCATENATE', 'CONCATENER', 'IF', 'SI', 'MAX', 'MIN', 'OR', 'OU', 'POW', 'PUISSANCE', 'ROUND', 'ARRONDI', 'WORLD'),
                'service' => 'test2',
            ),
            array(
                'config' => 'config-3',
                'functions' => array('constant', 'AND', 'ET', 'CONCATENATE', 'CONCATENER', 'IF', 'SI', 'MAX', 'MIN', 'OR', 'OU', 'POW', 'PUISSANCE', 'ROUND', 'ARRONDI', 'WORLD'),
                'service' => 'test3_1',
            ),
            array(
                'config' => 'config-3',
                'functions' => array('constant', 'AND', 'ET', 'CONCATENATE', 'CONCATENER', 'IF', 'SI', 'MAX', 'MIN', 'OR', 'OU', 'POW', 'PUISSANCE', 'ROUND', 'ARRONDI', 'MONDE'),
                'service' => 'test3_2',
            ),
        );
    }

    /**
     * @dataProvider getFunctions
     *
     * @param string $config
     * @param array  $functions
     * @param string $service
     */
    public function testGetFunctions($config, $functions, $service)
    {
        $this->loadConfiguration($this->container, $config);
        $this->container->compile();

        $formulaInterpretor = $this->container->get("profideo.formula_interpretor.excel.$service");

        $this->assertSame(
            $functions,
            $formulaInterpretor->getFunctions()
        );
    }

    public function testMultiConfig()
    {
        $this->loadConfiguration($this->container, 'config-3');
        $this->container->compile();

        $formulaInterpretor1 = $this->container->get('profideo.formula_interpretor.excel.test3_1');
        $formulaInterpretor2 = $this->container->get('profideo.formula_interpretor.excel.test3_2');

        $this->assertSame('HELLO WORLD !', $formulaInterpretor1->evaluate('CONCATENATE(HELLO;" ";WORLD())'));
        $this->assertSame('BONJOUR LE MONDE !', $formulaInterpretor2->evaluate('CONCATENATE(BONJOUR;" ";MONDE())'));
    }

    public function getMultiConfigConstants()
    {
        return array(
            array(
                'service' => 'test3_1',
                'constant' => 'HELLO',
                'result' => 'HELLO',
            ),
            array(
                'service' => 'test3_1',
                'constant' => 'BONJOUR',
                'result' => null,
                'exception' => 'Variable "BONJOUR" is not valid around position 1',
            ),
            array(
                'service' => 'test3_2',
                'constant' => 'BONJOUR',
                'result' => 'BONJOUR',
            ),
            array(
                'service' => 'test3_2',
                'constant' => 'HELLO',
                'result' => null,
                'exception' => 'Variable "HELLO" is not valid around position 1',
            ),
        );
    }

    /**
     * @dataProvider getMultiConfigConstants
     *
     * @param $service
     * @param $constant
     * @param $expectedResult
     * @param null $exception
     */
    public function testMultiConfigConstants($service, $constant, $expectedResult, $exception = null)
    {
        $this->loadConfiguration($this->container, 'config-3');
        $this->container->compile();

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\Component\ExpressionLanguage\SyntaxError',
                $exception
            );
        }

        $formulaInterpretor = $this->container->get("profideo.formula_interpretor.excel.$service");

        $result = $formulaInterpretor->evaluate($constant);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }

    public function testServicesConfig()
    {
        $serviceTest1 = new Definition();
        $serviceTest1->setClass('Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage\Fixtures\ServiceTest1');
        $this->container->setDefinition('profideo_formula_interpretor.test1', $serviceTest1);

        $serviceTest2 = new Definition();
        $serviceTest2->setClass('Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage\Fixtures\ServiceTest2');
        $this->container->setDefinition('profideo_formula_interpretor.test2', $serviceTest2);

        $serviceTest3 = new Definition();
        $serviceTest3->setClass('Profideo\FormulaInterpretorBundle\Tests\Excel\ExpressionLanguage\Fixtures\ServiceTest3');
        $this->container->setDefinition('profideo_formula_interpretor.test3', $serviceTest3);

        $this->loadConfiguration($this->container, 'config-4');
        $this->container->compile();

        $formulaInterpretor = $this->container->get('profideo.formula_interpretor.excel.test4');

        $this->assertSame('test 1 test 2 test 3', $formulaInterpretor->evaluate('test()'));
    }

    public function useDefaultFunctionsConstantsDataProvider()
    {
        return array(
            array(
                'expression' => '=OR(TRUE)',
                'result' => null,
                'config' => 'config-5',
                'service' => 'test5_1',
                'exception' => 'The function "OR" does not exist around position 1.',
            ),
            array(
                'expression' => '=OR(TRUE)',
                'result' => true,
                'config' => 'config-5',
                'service' => 'test5_2',
            ),
            array(
                'expression' => '=AND(OR(TRUE), TRUE)',
                'result' => null,
                'config' => 'config-5',
                'service' => 'test5_2',
                'exception' => 'The function "AND" does not exist around position 1.',
            ),
            array(
                'expression' => '=OR(TRUE)',
                'result' => true,
                'config' => 'config-5',
                'service' => 'test5_3',
            ),
            array(
                'expression' => '=AND(OR(TRUE), TRUE)',
                'result' => true,
                'config' => 'config-5',
                'service' => 'test5_3',
            ),
            array(
                'expression' => '=VRAI',
                'result' => null,
                'config' => 'config-5',
                'service' => 'test5_4',
                'exception' => 'Variable "VRAI" is not valid around position 1.',
            ),
            array(
                'expression' => '=HELLO',
                'result' => 'HELLO',
                'config' => 'config-5',
                'service' => 'test5_5',
            ),
            array(
                'expression' => '=VRAI',
                'result' => null,
                'config' => 'config-5',
                'service' => 'test5_5',
                'exception' => 'Variable "VRAI" is not valid around position 1.',
            ),
            array(
                'expression' => '=HELLO',
                'result' => 'HELLO',
                'config' => 'config-5',
                'service' => 'test5_6',
            ),
            array(
                'expression' => '=VRAI',
                'result' => true,
                'config' => 'config-5',
                'service' => 'test5_6',
            ),
            array(
                'expression' => '=VRAI',
                'result' => true,
                'config' => 'config-5',
                'service' => 'test5_7',
            ),
        );
    }

    /**
     * @dataProvider useDefaultFunctionsConstantsDataProvider
     *
     * @param $expression
     * @param $expectedResult
     * @param string $config
     * @param string $service
     * @param null $exception
     */
    public function testUseDefaultFunctionsConstants($expression, $expectedResult, $config, $service, $exception = null)
    {
        $this->loadConfiguration($this->container, $config);
        $this->container->compile();

        $formulaInterpretor = $this->container->get("profideo.formula_interpretor.excel.$service");

        if (null !== $exception) {
            $this->setExpectedException(
                '\Profideo\Component\ExpressionLanguage\SyntaxError',
                $exception
            );
        }

        $result = $formulaInterpretor->evaluate($expression);

        if (!$exception) {
            $this->assertSame($result, $expectedResult);
        }
    }
}
