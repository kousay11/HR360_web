<?php
namespace App\Controller;

use App\Entity\Equipe;
use App\Service\WorkloadGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WorkloadCalendarController extends AbstractController
{
    #[Route('/equipe/{id}/charge', name: 'workload_calendar')]
    public function show(Equipe $equipe)
    {
        return $this->render('workload_calendar/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/api/workload_events/{id}', name: 'workload_events')]
    public function getWorkloadEvents(Equipe $equipe, WorkloadGenerator $calculator): JsonResponse
    {
        $events = [];
        
        foreach ($calculator->getDailyWorkload($equipe) as $date => $projects) {
            $events[] = [
                'title' => count($projects) . ' projet(s)',
                'start' => $date,
                'backgroundColor' => $calculator->getColorCode(count($projects)),
                'extendedProps' => [
                    'projects' => $projects
                ]
            ];
        }
        
        return $this->json($events);
    }
}