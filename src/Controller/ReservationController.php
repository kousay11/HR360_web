<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Ressource;
use App\Entity\Utilisateur;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    private $entityManager;

    // Injection de l'EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // Récupérer l'utilisateur ayant ID = 1 pour les tests
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find(1);
        
        // Filtrer les réservations par utilisateur
        $reservations = $reservationRepository->findBy(['utilisateur' => $utilisateur]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }


    #[Route('/new/{ressourceId}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager , ?int $ressourceId = null): Response
    {





        $reservation = new Reservation();


        $ressource = $entityManager->getRepository(Ressource::class)->find($ressourceId);
        if($ressource){
            $reservation->setRessource($ressource);
        }
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find(1);
        if($utilisateur){
            $reservation->setUtilisateur($utilisateur);
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            
        ]);
        $form->handleRequest($request);
    

        

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'ressourceID' => $ressourceId
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}