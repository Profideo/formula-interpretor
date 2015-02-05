<?php
require_once dirname(__FILE__) . '/../../../vendor/phpoffice/phpexcel/Classes/PHPExcel/Calculation.php';
require_once dirname(__FILE__) . '/../../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';


$cell1 = 'A1';
$cell2 = 'B1';
$cell3 = 'C1';
$formula = "=SUM(A1:B1)";
//  enable debugging
PHPExcel_Calculation::getInstance()->writeDebugLog = true;

$objPHPExcel = new PHPExcel();

$sheet = $objPHPExcel->getActiveSheet();
$sheet->getCell($cell1)->setValue(1);
$sheet->getCell($cell2)->setValue(2);

//$formulaValue = $sheet->getCell($cell)->getValue();
//echo '<b>'.$cell.' Value is </b>'.$formulaValue."<br />\n";
echo '<b>'.$cell1.' Value is </b>'.$sheet->getCell($cell1)->getValue()."<br />\n";
echo '<b>'.$cell2.' Value is </b>'.$sheet->getCell($cell2)->getValue()."<br />\n";
echo '<b>'.$cell3.' Value is </b>'.$sheet->getCell($cell3)->getValue()."<br />\n";

$calculate = false;
try {
    $tokens = PHPExcel_Calculation::getInstance()->parseFormula($formula);
    echo '<b>Parser Stack :-</b><pre>';
    print_r($tokens);
    echo '</pre>';
    $calculate = true;
} catch (Exception $e) {
    echo "PARSER ERROR: ".$e->getMessage()."<br />\n";

    echo '<b>Parser Stack :-</b><pre>';
    print_r($tokens);
    echo '</pre>';
}

if ($calculate) {
    //  calculate
    try {
        $sheet->setCellValue($cell3, $formula);
        echo '<b>'.$cell1.' Value is </b>'.$sheet->getCell($cell1)->getValue()."<br />\n";
        echo '<b>'.$cell2.' Value is </b>'.$sheet->getCell($cell2)->getValue()."<br />\n";
        echo '<b>'.$cell3.' Value is </b>'.$sheet->getCell($cell3)->getCalculatedValue()."<br />\n";
    } catch (Exception $e) {
        echo "CALCULATION ENGINE ERROR: ".$e->getMessage()."<br />\n";

        echo '<h3>Evaluation Log:</h3><pre>';
        print_r(PHPExcel_Calculation::getInstance()->debugLog);
        echo '</pre>';
    }
}