<?php
namespace Profideo\FormulaInterpretorBundle\Calculation;

use \PHPExcel_Calculation;

/**
 * Class AbstractCalculationClient
 * @package Profideo\FormulaInterpretor
 */
abstract class AbstractCalculationClient extends PHPExcel_Calculation
{
    protected static $instance;

    private static $_allowedFunctions = [
        'MIN',
        'MAX',
        'POWER',
        'ROUND',
        'CONCATENATE',
        'COS',
        'IF',
        'OR',
        'AND',
        'CNA',
        'CNC',
        'CNANC'
    ];

    public static function getCalculationInstance()
    {
        if (!isset(self::$instance) || (self::$instance === NULL)) {
            self::$instance = parent::getInstance();
            self::$instance->setLocale('fr');
        }

        self::$_PHPExcelFunctions = array_merge(self::$_PHPExcelFunctions, [
            'CNA'       			=> array('category'			=>	CalculationCustom::CATEGORY_CUSTOM,
                'functionCall'		=>	__NAMESPACE__ . '\CalculationCustom::CNA',
                'argumentCount'	=>	'1'
            ),
            'CNC'       			=> array('category'			=>	CalculationCustom::CATEGORY_CUSTOM,
                'functionCall'		=>	__NAMESPACE__ . '\CalculationCustom::CNC',
                'argumentCount'	=>	'1'
            ),
            'CNANC'       			=> array('category'			=>	CalculationCustom::CATEGORY_CUSTOM,
                'functionCall'		=>	__NAMESPACE__ . '\CalculationCustom::CNANC',
                'argumentCount'	=>	'1'
            ),
        ]);


        return self::$instance;
    }

    /**
     * Checks if the formula is valid. Return false if not and yes if the formula is valid
     *
     * @param $formula
     * @return bool
     */
    public static function isFormulaValid($formula)
    {
        try {
            $formula_parts = self::getCalculationInstance()->parseFormula($formula);

            foreach($formula_parts as $formula_part) {
                $php_excel_formulas = self::getCalculationInstance()->listAllFunctionNames();

                if($formula_part['type'] == "Function" &&
                    in_array(str_replace("(", "", $formula_part['value']), $php_excel_formulas) &&
                    !in_array(str_replace("(", "", $formula_part['value']), self::$_allowedFunctions)
                ) {
                    throw new NotAllowedFormulaException(sprintf("La fonction %s n'est pas autorisée", str_replace("(", "", $formula_part['value'])));
                }
            }
        } catch (CalculationException $e) {
            return false;
        } catch (NotAllowedFormulaException $e) {
            var_dump($e->getMessage());
            return false;
        }
        return true;
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

        return self::getCalculationInstance()->_calculateFormulaValue($formula);
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
    private static function replaceFieldsValuesInFormula($formula, $fieldIds, $values)
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

    /**
     * Returns fieldsIds in the string given
     * FieldIds are represented by C#### -> # is numeric character
     * @param $str
     * @return array
     */
    private static function getFieldIds($str) {
        $fieldIds = [];
        preg_match_all('/C\d+/', $str, $matches);

        foreach($matches[0] as $match) {
            $fieldIds[] = explode('C', $match)[1];
        }
        return $fieldIds;
    }

} 