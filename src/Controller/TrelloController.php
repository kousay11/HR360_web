<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Repository\ProjetRepository;
use App\Service\TrelloApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use \DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class TrelloController extends AbstractController
{
    private TrelloApiService $trelloService;

    private EntityManagerInterface $entityManager;

    public function __construct(TrelloApiService $trelloService, EntityManagerInterface $entityManager)
    {
        $this->trelloService = $trelloService;
        $this->entityManager = $entityManager;
    }

    #[Route('/create-trello-board', name: 'create_TrelloBoard', methods: ['POST'])]
    public function createTrelloBoard(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $taskId = $data['task_id'] ?? null;
        $projectId = $data['project_id'] ?? null;

        if (!$taskId || !$projectId) {
            return $this->json([
                'success' => false,
                'message' => 'Missing task or project ID'
            ], 400);
        }

        try {
            $task = $this->entityManager
                ->getRepository(Tache::class)
                ->find($taskId);
            $task = $this->entityManager
                ->getRepository(Tache::class)
                ->find($taskId);

            if (!$task) {
                throw new \Exception('Task not found');
            }

            // Create board with date range
            $boardId = $this->trelloService->createBoardWithLists(
                $task->getNom(),
                $task->getDateDebut(),
                $task->getDateFin()
            );
            $this->entityManager->flush();
            // Save board ID to task
            $task->setTrelloboardid($boardId);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'boardId' => $boardId
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    #[Route('/disable-trello-board', name: 'disable_TrelloBoard', methods: ['POST'])]
public function disableTrelloBoard(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $taskId = $data['task_id'] ?? null;
    $boardId = $data['board_id'] ?? null;

    if (!$taskId || !$boardId) {
        return $this->json([
            'success' => false,
            'message' => 'Missing task or board ID'
        ], 400);
    }

    try {
        $task = $this->entityManager
            ->getRepository(Tache::class)
            ->find($taskId);

        if (!$task) {
            throw new \Exception('Task not found');
        }

        // Delete the Trello board
        $deleteSuccess = $this->trelloService->deleteBoard($boardId);
        
        if (!$deleteSuccess) {
            throw new \Exception('Failed to delete Trello board');
        }

        // Clear board ID from task
        $task->setTrelloboardid(null);
        $this->entityManager->flush();

        return $this->json([
            'success' => true
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}