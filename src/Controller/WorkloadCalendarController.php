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

    #[Route('/api/equipe/{id}/charge/events', name: 'workload_events')]
    public function getEvents(Equipe $equipe, WorkloadGenerator $generator, Request $request)
    {
        // Filtrage par période si nécessaire
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $events = $generator->generate($equipe);
        
        return new JsonResponse($events);
    }
}