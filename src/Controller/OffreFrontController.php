<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offreFront')]
final class OffreFrontController extends AbstractController
{
    #[Route(name: 'app_offreFront_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository): Response
    {
        // Met à jour les statuts avant d'afficher
        $offreRepository->updateExpiredStatus();
        
        return $this->render('offreFront/index.html.twig', [
            'offres' => $offreRepository->findAll(),
        ]);
    }
    #[Route('/{idoffre}', name: 'app_offreFront_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offreFront/show.html.twig', [
            'offre' => $offre,
        ]);
    }
}
