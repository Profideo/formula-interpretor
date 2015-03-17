<?php
namespace Profideo\FormulaInterpretorBundle\Calculation;

use \PHPExcel_Calculation;

/**
 * Class AbstractCalculationClient
 * @package Profideo\FormulaInterpretor
 */
abstract class AbstractCalculationClient extends PHPExcel_Calculation
{
    const CATEGORY_CUSTOM				    = 'Custom';

    protected static $instance;

    private static $_allowedFunctions = [
        'MIN',
        'MAX',
        'POWER',
        'ROUND',
        'CONCATENATE',
        'IF',
        'OR',
        'AND'
    ];

    /**
     * Defines a custom function and allow it
     *
     * @example :
     *
     * addCustomFunction('CNA', '\Pfd\AppBundle\CustomFormulas\CalculationCustom::CNA', '1-2')
     * function named CNA, defined in \Pfd\AppBundle\CustomFormulas\CalculationCustom, method CNA
     * which needs a required argument and an optional one
     *
     * @param $formulaName
     * @param $method The entire method name with namespace
     * @param $argumentsCount The number of arguments needed by the function
     */
    public static function addCustomFunction($formulaName, $method, $argumentsCount)
    {
        self::$_PHPExcelFunctions = array_merge(self::$_PHPExcelFunctions, [
            $formulaName       		=> array(
                'category'			=>	self::CATEGORY_CUSTOM,
                'functionCall'		=>	$method,
                'argumentCount'	    =>	$argumentsCount
            )
        ]);

        array_push(self::$_allowedFunctions, $formulaName);
    }

    private static function getCalculationInstance()
    {
        if (!isset(self::$instance) || (self::$instance === NULL)) {
            self::$instance = parent::getInstance();
            self::$instance->setLocale('fr');
        }

        return self::$instance;
    }

    public static function translateFormula($formula)
    {
        return self::getCalculationInstance()->_translateFormulaToEnglish($formula);
    }

    /**
     * Checks if the formula is valid by splitting it in logical parts
     *
     * @param $formula
     *
     * @return array
     */
    public static function isFormulaValid($formula)
    {
        return self::getCalculationInstance()->parseFormula($formula);
    }

    /**
     * Return the result of a formula.
     * Set cellValues in each column of first line and execute formula
     *
     * @example
     *      cellValues = [1, 12, 34, 56]
     * Set  A1 = 1
     *      B1 = 12
     *      C1 = 34
     *      D1 = 56
     * Set formula in Z999 and return the formula result according to A1, B1, C1, D1
     *
     * @param $cellValues
     * @param $formula
     * @return mixed
     * @throws \PHPExcel_Calculation_Exception
     * @throws \PHPExcel_Exception
     */
    public static function getFormulaResult($cellValues, $formula)
    {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        $tmpformula = static::fillCellValuesAndFixFormula($formula, $cellValues, $sheet);

        $calculationClient = self::getInstance($objPHPExcel);

        return $calculationClient->calculateFormula($tmpformula, 'Z999', $sheet->getCell('Z999'));
    }

    /**
     * Set cellValues in each column of first line
     * If a treatment needs to be done when a cell is filled, for example modify the formula
     * or if the cellValues array is not uni-dimensional, you need to override this method
     *
     * @param $formula
     * @param $cellValues
     * @param $sheet
     * @param int $startCellId
     * @return mixed
     */
    public static function fillCellValuesAndFixFormula($formula, $cellValues, $sheet, $startCellId = 0)
    {
        $column = $startCellId;

        foreach ($cellValues as $value) {
            // For better performances, we always stay on the first row of the sheet.
            $cell = $sheet->getCellByColumnAndRow($column, 1);
            $cell->setValue($value);

            $column++;
        }

        return $formula;
    }
} 