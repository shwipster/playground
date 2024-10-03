<?php

namespace App\Controller;


use App\Patterns\FactoryMethod\ChairPricings;
use App\Patterns\FactoryMethod\TablePricings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PatternsController extends AbstractController
{
    #[Route('/patterns', name: 'app_patterns')]
    public function index(): Response
    {
        //Factory method
        $chairPricing = new ChairPricings();
        $tablePricing = new TablePricings();

        return $this->render('patterns/index.html.twig', [
            'controller_name' => 'PatternsController',
            'chairPricing' => $chairPricing->normalPrice(),
            'tablePricing' => $tablePricing->normalPrice(),
        ]);
    }
}
