<?php

namespace AppBundle\Controller;

use AppBundle\Calculation\CalculationClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/testformula", name="test_formula")
     */
    public function testFormula()
    {
        //$formula = '=SI(C123<>C456=VRAI, "pouet", "plop")';
        $formula = '=SI(CNA(C456), "pouet", "plop")';

        $calculation = CalculationClient::getInstance();

        $value = CalculationClient::getFormulaResult($formula, ['123' => 11, '456' => 11.9999]);

        return new Response(" ====> " . $value . " <==== ");
    }
}
