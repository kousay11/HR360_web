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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\QRCodeService;
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
    public function new(Request $request, EntityManagerInterface $entityManager, RessourceRepository $ressourceRepository, ?int $ressourceId = null, QRCodeService $qrCodeService): Response
    {
        $reservation = new Reservation();
        $ressource = $entityManager->getRepository(Ressource::class)->find($ressourceId);

        if ($ressource) {
            $reservation->setRessource($ressource);
        }
        $utilisateur = $this->getUser();
        if($utilisateur){
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
            $qrCodeService->generateQRCodeForRessource($reservation->getRessource());
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


    #[Route('/history', name: 'app_reservation_history', methods: ['GET'])]
public function history(ReservationRepository $reservationRepository): Response
{
    /** @var Utilisateur $user */
    $user = $this->getUser();
    $now = new \DateTime();

    // Récupération des statistiques de base
    $stats = $reservationRepository->getUserStats($user);
        // Récupérer les données
        $upcoming = $reservationRepository->findUpcoming($user, $now);
        $active = $reservationRepository->findActive($user, $now);
        $completed = $reservationRepository->findCompleted($user, $now);
    // Préparation des données pour les graphiques
    $reservationsByMonth = array_fill(0, 12, 0); // Index 0-11 pour Jan-Déc
    $averageDurationByMonth = array_fill(0, 12, 0);
    
    $totalDurationByMonth = array_fill(0, 12, 0);
    $countByMonth = array_fill(0, 12, 0);

    $reservations = $reservationRepository->findBy(['utilisateur' => $user]);

    foreach ($reservations as $reservation) {
        if ($reservation->getDatedebut() && $reservation->getDatefin()) {
            $start = $reservation->getDatedebut();
            $monthIndex = (int)$start->format('n') - 1; // Convertir 1-12 en 0-11
            
            // Calcul durée
            $interval = $start->diff($reservation->getDatefin());
            $duration = $interval->days + 1; // Inclure le jour de départ

            // Mise à jour des compteurs
            $reservationsByMonth[$monthIndex]++;
            $totalDurationByMonth[$monthIndex] += $duration;
            $countByMonth[$monthIndex]++;
        }
    }

    // Calcul des moyennes
    foreach ($countByMonth as $month => $count) {
        if ($count > 0) {
            $averageDurationByMonth[$month] = round($totalDurationByMonth[$month] / $count, 1);
        }
    }

    return $this->render('reservation/history.html.twig', [
        'upcoming' => $upcoming,
        'active' => $active,
        'completed' => $completed,
        'stats' => $stats,
        'reservations_by_month' => array_values($reservationsByMonth),
        'average_duration_by_month' => array_values($averageDurationByMonth),
        'months' => [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ],
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
        'recommendations' => $recommendations,
         // ➔ NE PAS OUBLIER de passer recommendations ici
    ]);
}


#[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
public function delete(
    Request $request,
    Reservation $reservation,
    EntityManagerInterface $entityManager,
    QRCodeService $qrCodeService
): Response {
    // Vérifie le token CSRF
    if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
        
        // Vérifie si la réservation commence dans moins de 24h
        $now = new \DateTime();
        $debut = $reservation->getDateDebut(); // Assure-toi que c’est bien le champ datetime

        $intervalSeconds = $debut->getTimestamp() - $now->getTimestamp();

        if ($intervalSeconds < 86400) {
            $this->addFlash('warning', 'Vous ne pouvez pas annuler une réservation qui commence dans moins de 24 heures.');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        // Supprimer la réservation
        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');
    }

    return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
}



#[Route('/api/events/new', name: 'api_event_new', methods: ['POST'])]
public function newEvent(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $reservation = new Reservation();
    
    // Conversion des dates
    try {
        $startDate = new \DateTime($data['start']);
        $endDate = new \DateTime($data['end']);
    } catch (\Exception $e) {
        return $this->json(['error' => 'Format de date invalide'], 400);
    }

    $reservation->setDatedebut($startDate);
    $reservation->setDatefin($endDate);
    
    // Gestion de la ressource
    $ressource = $em->getRepository(Ressource::class)->find($data['resourceId']);
    if (!$ressource) {
        return $this->json(['error' => 'Ressource introuvable'], 404);
    }
    $reservation->setRessource($ressource);

    // Validation manuelle

    // Vérification des conflits
    if ($this->hasConflict($reservation, $em)) {
        return $this->json(['error' => 'Conflit de réservation'], 409);
    }

    $em->persist($reservation);
    $em->flush();

    return $this->json([
        'id' => $reservation->getId(),
        'message' => 'Réservation créée avec succès'
    ]);
}

private function hasConflict(Reservation $newReservation, EntityManagerInterface $em): bool
{
    $existingReservations = $em->getRepository(Reservation::class)
        ->findByRessource($newReservation->getRessource());

    foreach ($existingReservations as $existing) {
        if ($existing->conflictsWith(
            $newReservation->getDatedebut(), 
            $newReservation->getDatefin()
        )) {
            return true;
        }
    }
    return false;
}

    
    
}