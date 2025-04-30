<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

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
}
