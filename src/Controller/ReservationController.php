<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Ressource;
use App\Entity\Utilisateur;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\RessourceRepository; // ✅ Ajout
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection; // ✅ Ajout pour PARAM_INT_ARRAY
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
    public function new(Request $request, EntityManagerInterface $entityManager, RessourceRepository $ressourceRepository, ?int $ressourceId = null): Response
    {
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

        // 👉 Fetch des dates réservées
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
                    (clone $end)->modify('+1 day') // Inclure date de fin
                );

                foreach ($period as $date) {
                    $reservedDates[] = $date->format('Y-m-d');
                }
            }
        }
        $reservedDates = array_values(array_unique($reservedDates));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Stripe
            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            $prixRessource = $ressource->getPrix();
            $montantCentimes = intval($prixRessource * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $montantCentimes,
                'currency' => 'eur',
                'metadata' => ['reservation_id' => $reservation->getId()],
            ]);

            return $this->render('reservation/payment.html.twig', [
                'reservation' => $reservation,
                'clientSecret' => $paymentIntent->client_secret,
                'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            ]);
        }

        // ✅ Suggestion de ressources similaires
        $excludedIds = [$ressource->getId()];
        $similarResources = $ressourceRepository->createQueryBuilder('r')
            ->where('r.id NOT IN (:excluded)')
            ->andWhere('r.type = :type')
            ->setParameter('excluded', $excludedIds, Connection::PARAM_INT_ARRAY) // ✅ Correct
            ->setParameter('type', $ressource->getType())
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'ressourceID' => $ressourceId,
            'reservedDates' => $reservedDates,
            'similarResources' => $similarResources, // ✅ Envoi au template
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
public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, RessourceRepository $ressourceRepository): Response
{
    $form = $this->createForm(ReservationType::class, $reservation);
    $form->handleRequest($request);

    $reservationsExistantes = $entityManager->getRepository(Reservation::class)
        ->findBy(['ressource' => $reservation->getRessource()]);

    $reservedDates = [];
    foreach ($reservationsExistantes as $res) {
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

    // ➔ Ajouter suggestions de ressources similaires (comme dans new)
    $excludedIds = [$reservation->getRessource()->getId()];
    $recommendations = $ressourceRepository->createQueryBuilder('r')
        ->where('r.id NOT IN (:excluded)')
        ->andWhere('r.type = :type')
        ->setParameter('excluded', $excludedIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
        ->setParameter('type', $reservation->getRessource()->getType())
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('reservation/edit.html.twig', [
        'reservation' => $reservation,
        'form' => $form,
        'reservedDates' => $reservedDates,
        'recommendations' => $recommendations, // ➔ NE PAS OUBLIER de passer recommendations ici
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