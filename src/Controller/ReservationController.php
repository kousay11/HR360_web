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
use Stripe\Stripe;
use Stripe\PaymentIntent;
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
        $utilisateur = $this->getUser();
        
        // Filtrer les réservations par utilisateur
        $reservations = $reservationRepository->findBy(['utilisateur' => $utilisateur]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }


    #[Route('/new/{ressourceId}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ?int $ressourceId = null): Response
{
    // Création de la réservation
    $reservation = new Reservation();
    $ressource = $entityManager->getRepository(Ressource::class)->find($ressourceId);
    if ($ressource) {
        $reservation->setRessource($ressource);
    }
    $utilisateur = $this->getUser();
    if ($utilisateur) {
        $reservation->setUtilisateur($utilisateur);
    }

    $form = $this->createForm(ReservationType::class, $reservation);
    $form->handleRequest($request);



    // 👉 Fetch des dates réservées pour cette ressource
    $reservationsExistantes = $entityManager->getRepository(Reservation::class)
        ->findBy(['ressource' => $ressource]);

    $reservedDates = [];
    foreach ($reservationsExistantes as $res) {
        $start = $res->getDatedebut();
        $end = $res->getDatefin();

        if ($start && $end) {
            $period = new \DatePeriod(
                $start,
                new \DateInterval('P1D'),
                (clone $end)->modify('+1 day') // inclure la date de fin
            );

            foreach ($period as $date) {
                $reservedDates[] = $date->format('Y-m-d');
            }
        }
    }

    // Pour enlever les doublons éventuels
    $reservedDates = array_values(array_unique($reservedDates));




    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reservation);
        $entityManager->flush();

        // Étape Stripe : créer un PaymentIntent après la création de la réservation
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']); // Assure-toi que cette clé est définie dans .env


        $prixRessource = $ressource->getPrix(); // Exemple : 25.00
        $montantCentimes = intval($prixRessource * 100); // Exemple : 2500

        $paymentIntent = PaymentIntent::create([
        'amount' => $montantCentimes,
        'currency' => 'eur',
        'metadata' => ['reservation_id' => $reservation->getId()],
    ]);

        // Envoie la clé publique de Stripe au frontend pour créer le paiement
        return $this->render('reservation/payment.html.twig', [
            'reservation' => $reservation,
            'clientSecret' => $paymentIntent->client_secret,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
        ]);
        
        
    }

    return $this->render('reservation/new.html.twig', [
        'reservation' => $reservation,
        'form' => $form,
        'ressourceID' => $ressourceId,
        'reservedDates' => $reservedDates,
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

    // 👉 Calculer les dates réservées (comme dans new)
    $reservationsExistantes = $entityManager->getRepository(Reservation::class)
        ->findBy(['ressource' => $reservation->getRessource()]);

    $reservedDates = [];
    foreach ($reservationsExistantes as $res) {
        // Exclure la réservation actuelle (sinon conflit sur ses propres dates)
        if ($res->getId() !== $reservation->getId()) {
            $start = $res->getDatedebut();
            $end = $res->getDatefin();

            if ($start && $end) {
                $period = new \DatePeriod(
                    $start,
                    new \DateInterval('P1D'),
                    (clone $end)->modify('+1 day')
                );

                foreach ($period as $date) {
                    $reservedDates[] = $date->format('Y-m-d');
                }
            }
        }
    }
    $reservedDates = array_values(array_unique($reservedDates));

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('reservation/edit.html.twig', [
        'reservation' => $reservation,
        'form' => $form,
        'reservedDates' => $reservedDates, // 👈 ajoute cette ligne
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