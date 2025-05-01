<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reservation;
use App\Repository\RessourceRepository;

final class DashboardController extends AbstractController
{


    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(ReservationRepository $repo, EntityManagerInterface $em): Response
    {
        // Récupérer les statistiques par période (semaine et mois)
        $weeklyStats = $repo->countReservationsByPeriod('week');
        $monthlyStats = $repo->countReservationsByPeriod('month');
        $mostRequested = $repo->getMostRequestedResource();
    
        // Préparer les données pour les graphiques
        $labels = array_column($monthlyStats, 'period');
        $data = array_column($monthlyStats, 'count');
    
        // Récupérer les sources de réservation
        $resourceTypes = $em->createQuery(
            'SELECT r.type AS type, COUNT(res.id) AS count
             FROM App\Entity\Reservation res
             JOIN res.ressource r
             GROUP BY r.type'
        )->getResult();

        // Préparer les labels et les données du graphique circulaire
        $pieLabels = [];
        $pieData = [];

        foreach ($resourceTypes as $entry) {
            $pieLabels[] = $entry['type'];
            $pieData[] = $entry['count'];
        }

        return $this->render('dashboard/index.html.twig', [
            'weeklyStats' => $weeklyStats,
            'monthlyStats' => $monthlyStats,
            'mostRequested' => $mostRequested,
            'chartLabels' => json_encode($labels),
            'chartData' => json_encode($data),
            'pieLabels' => json_encode($pieLabels),
            'pieData' => json_encode($pieData),
        ]);
    }

    #[Route('/dashboard/events', name: 'app_dashboard_events')]
public function events(ReservationRepository $reservationRepository): JsonResponse
{
    $reservations = $reservationRepository->findAll();
    $events = [];

    foreach ($reservations as $reservation) {
        // Dans le contrôleur PHP
$start = $reservation->getDateDebut()
->setTimezone(new \DateTimeZone('UTC'))
->format('Y-m-d');
        $end = $reservation->getDateFin()->modify('+1 day')->format('Y-m-d');
        
        $events[] = [
            'title' => $reservation->getRessource()->getNom(),
            'start' => $start,
            'end' => $end,
            'description' => 'Réservation de ' . $reservation->getUtilisateur()->getNom(),
            'display' => 'block', // Ajout optionnel pour le style
        ];
    }

    return new JsonResponse($events);

}

#[Route('/reservations/update/{id}', name: 'app_reservation_update', methods: ['PUT'])]
    public function updateEvent(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Récupérer les données de la requête
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les dates
            if (isset($data['start'])) {
                $startDate = \DateTime::createFromFormat(\DateTime::ISO8601, $data['start']);
                $reservation->setDateDebut($startDate);
            }

            if (isset($data['end']) && !empty($data['end'])) {
                $endDate = \DateTime::createFromFormat(\DateTime::ISO8601, $data['end']);
                $reservation->setDateFin($endDate);
            }

            $em->persist($reservation);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Réservation mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }




    #[Route('/api/resources', name: 'api_resources')]
public function getResources(RessourceRepository $repo): JsonResponse
{
    return $this->json($repo->findAll(), 200, [], ['groups' => 'calendar']);
}

#[Route('/calendar/updates', name: 'calendar_updates')]
public function sseUpdates(ReservationRepository $repo): Response
{
    return new Response(
        $repo->streamUpdates(),
        200,
        [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]
    );
}

}
