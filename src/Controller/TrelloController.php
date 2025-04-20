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

class TrelloController extends AbstractController
{
    #[Route('/create-board/{id}', name: 'create_TrelloBoard')]
    public function createBoard(TrelloApiService $trelloService,Tache $tache,EntityManagerInterface $entityManager,ProjetRepository $projetRepository): Response
    {
        $startDate = $tache->getDateDebut();
        $endDate = $tache->getDateFin();
        $projet=$tache->getProjet();
        $projetName = $projet->getNom();
        $BoardName = $projetName . ' - ' . $tache->getNom();
        $boardId = $trelloService->createBoardWithLists($BoardName, $startDate, $endDate);
        if ($boardId) {
            $tache->setTrelloboardid($boardId);
            $entityManager->persist($tache);
            $entityManager->flush();
            // Add members
            $trelloService->addMembersToBoard($boardId,$projetRepository->findEquipeEmails($projet));
            
            return $this->json(['status' => 'success', 'boardId' => $boardId]);
        }
        
        return $this->json(['status' => 'error'], 500);
    }
}