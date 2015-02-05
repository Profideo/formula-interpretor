# Profideo Formula Interpreter


This bundle is used to interprete excel based formulas

It requires phpoffice/phpexcel

How to use :

1 - Add "profideo/formula-interpretor": "dev-master" in your composer.json

2 - Add "php app/console excelformulas:changePhpExcelAttributeVisibility" in "post-install-cmd" and "post-update-cmd"

3 - Create your class which extends "Profideo\FormulaInterpretor\AbstractCalculationClient"


Example of usage :

     $formula = '=SI(CNA(C456), "pouet", "plop")';

     $value = CalculationClient::getFormulaResult($formula, ['123' => 11]);

     return new Response(" ====> " . $value . " <==== ");