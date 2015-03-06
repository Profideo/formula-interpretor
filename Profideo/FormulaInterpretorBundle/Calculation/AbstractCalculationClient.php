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
     * Checks if the formula is valid.
     *
     * @param $formula
     *
     * @return array
     */
    public static function isFormulaValid($formula)
    {
        return self::getFormulaParts($formula);
    }

    /**
     * Replace fieldsIds by values given,
     * Checks if the formula is valid,
     * If it is, calculate and return formula value
     *
     * @param $formula
     * @param $values
     * @return mixed|null|void
     * @throws \Exception
     */
    public static function getFormulaResult($formula, $values)
    {
        if(empty($formula)) {
            throw new \Exception("Formula cannot be empty");
        }

        $formula = self::getCalculationInstance()->_translateFormulaToEnglish($formula);

        $fieldIds = self::getFieldIds($formula);

        $formula = self::replaceFieldsValuesInFormula($formula, $fieldIds, $values);

        if(!self::isFormulaValid($formula)) {
            throw new \Exception("The formula is not good");
        }

        return self::getCalculationInstance()->_calculateFormulaValue($formula, null, null);
    }

    /**
     * Check if all fields (in the formula) are if the array of values given
     *
     * @param $fieldIds
     * @param $values
     * @return bool
     */
    private static function valuesComplete($fieldIds, $values)
    {
        foreach($fieldIds as $fieldId) {
            //check if the reference value is given
            if(!array_key_exists($fieldId, $values)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if all fields are in the array of values given
     * If true, replace fieldIds by theirs values
     *
     * @param $formula
     * @param $fieldIds
     * @param $values
     * @return mixed
     * @throws \Exception
     */
    public static function replaceFieldsValuesInFormula($formula, $fieldIds, $values)
    {
        if(!self::valuesComplete($fieldIds, $values)) {
            throw new \Exception("All the references are not in array of values");
        }

        foreach($fieldIds as $fieldId) {
            $formula = str_replace("C".$fieldId, $values[$fieldId], $formula);
        }

        return $formula;
    }

    /**
     * Get the characters between start and end patterns in the string given
     *
     * @param $string
     * @param $start
     * @param $end
     * @return string
     */
    private function get_string_between($string, $start, $end){
        $string = " ".$string;
        $ini = strpos($string,$start);

        if ($ini == 0) return "";

        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;

        return substr($string,$ini,$len);
    }

    public static function getFormulaParts($formula)
    {
        return self::getCalculationInstance()->parseFormula($formula);
    }

} 