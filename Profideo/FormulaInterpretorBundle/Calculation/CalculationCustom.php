<?php
namespace Profideo\FormulaInterpretorBundle\Calculation;

/**
 * Class CalculationCustom
 * @package Profideo\FormulaInterpretor
 */
class CalculationCustom
{
    const CATEGORY_CUSTOM				    = 'Custom';

     /**
     * CNA
     *
     * @param	mixed	$value		Value to check
     * @return	boolean
     */
    public static function CNA($value = NULL) {
        $value		= self::flattenSingleValue($value);

        if($value != '#NA') {
            return false;
        }
        return true;
    }	//	function CNA()

    /**
     * CNC
     *
     * @param	mixed	$value		Value to check
     * @return	boolean
     */
    public static function CNC($value = NULL) {
        $value		= self::flattenSingleValue($value);

        if($value != '#NC') {
            return false;
        }
        return true;
    }	//	function CNC()

    /**
     * CNANC
     *
     * @param	mixed	$value		Value to check
     * @return	boolean
     */
    public static function CNANC($value = NULL) {
        $value		= self::flattenSingleValue($value);

        if($value != '#NA' && $value != '#NC') {
            return false;
        }
        return true;
    }	//	function CNANC()


    /**
     * Convert an array to a single scalar value by extracting the first element
     *
     * @param	mixed		$value		Array or scalar value
     * @return	mixed
     */
    public static function flattenSingleValue($value = '') {
        while (is_array($value)) {
            $value = array_pop($value);
        }

        return $value;
    }	//	function flattenSingleValue()

} 