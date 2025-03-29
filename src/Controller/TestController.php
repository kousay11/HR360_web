<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        return $this->render('baseEMP.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
    #[Route('/testC', name: 'app_testc')]
    public function indexC(): Response
    {
        return $this->render('baseCAN.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
    #[Route('/testR', name: 'app_testr')]
    public function indexR(): Response
    {
        return $this->render('baseRH.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
