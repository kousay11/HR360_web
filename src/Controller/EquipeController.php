<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Equipeemploye;
use App\Form\EquipeemployeType;
use App\Entity\Utilisateur;
use App\Entity\Projetequipe;
use App\Entity\Projet;
use App\Service\TrelloApiService;
use App\Repository\TacheRepository;
use App\Repository\ProjetRepository;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route(name: 'app_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/report', name: 'app_equipe_report', methods: ['GET'])]
    public function generateReport(EquipeRepository $equipeRepository, ProjetRepository $projetRepository): Response
    {
        // Create response object with JSON content type
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        try {
            error_log("Starting report generation");

            // Get all teams at once
            $teams = $equipeRepository->findAll();
            if (empty($teams)) {
                error_log("No teams found");
                return new Response(json_encode(['error' => 'No teams found']), Response::HTTP_NOT_FOUND);
            }

            error_log("Found " . count($teams) . " teams");
            
            // Build complete report data structure
            $reportData = [
                'teams' => [],
                'generated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'total_teams' => count($teams)
            ];

            foreach ($teams as $team) {
                if (!$team) continue;

                $teamData = [
                    'name' => $team->getNom() ?? 'Unnamed Team',
                    'projects' => [],
                    'employees' => []
                ];

                // Get all projects for this team
                $projets = $projetRepository->findByEquipe($team);
                foreach ($projets as $projet) {
                    if (!$projet) continue;

                    $projectData = [
                        'name' => $projet->getNom() ?? 'Unnamed Project',
                        'description' => $projet->getDescription() ?? '',
                        'start_date' => $projet->getDateDebut() ? $projet->getDateDebut()->format('Y-m-d') : null,
                        'end_date' => $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : null,
                        'tasks' => []
                    ];

                    // Add all tasks for this project
                    foreach ($projet->getTaches() as $tache) {
                        if (!$tache) continue;
                        
                        $projectData['tasks'][] = [
                            'name' => $tache->getNom() ?? 'Unnamed Task',
                            'description' => $tache->getDescription() ?? '',
                            'start_date' => $tache->getDateDebut() ? $tache->getDateDebut()->format('Y-m-d') : null,
                            'end_date' => $tache->getDateFin() ? $tache->getDateFin()->format('Y-m-d') : null,
                            'status' => $tache->getStatut() ?? 'Unknown'
                        ];
                    }

                    $teamData['projects'][] = $projectData;
                }

                // Add all employees for this team
                foreach ($team->getEquipeemployes() as $equipeEmploye) {
                    if (!$equipeEmploye || !$equipeEmploye->getUtilisateur()) continue;

                    $employe = $equipeEmploye->getUtilisateur();
                    $teamData['employees'][] = [
                        'first_name' => $employe->getPrenom() ?? '',
                        'last_name' => $employe->getNom() ?? '',
                        'email' => $employe->getEmail() ?? ''
                    ];
                }

                $reportData['teams'][] = $teamData;
            }

            if (empty($reportData['teams'])) {
                error_log("No valid data found after processing");
                return new Response(json_encode(['error' => 'No valid data found']), Response::HTTP_NOT_FOUND);
            }

            error_log("Successfully generated complete report");
            return new Response(json_encode($reportData), Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log("Critical error in report generation: " . $e->getMessage());
            return new Response(json_encode([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search', name: 'app_equipe_search', methods: ['GET'])]
    public function search(Request $request, EquipeRepository $equipeRepository): Response
    {
        $query = $request->query->get('q', '');
        $equipes = $equipeRepository->search($query);

        if ($request->isXmlHttpRequest()) {
            return $this->render('equipe/_grid.html.twig', [
                'equipes' => $equipes,
            ]);
        }

        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/add-member', name: 'app_equipeemploye_new', methods: ['GET','POST'])]
    public function newMember(Request $request, Equipe $equipe, EntityManagerInterface $entityManager,TacheRepository $tache_repository,TrelloApiService $trello_api_service): Response
{
    $equipeEmploye = new Equipeemploye();
    $equipeEmploye->setEquipe($equipe); // Set the team ID
    
    $form = $this->createForm(EquipeemployeType::class, $equipeEmploye, ['equipe' => $equipe]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($equipeEmploye);
        $entityManager->flush();
        $email=$equipeEmploye->getUtilisateur()->getEmail();
        $taches=$tache_repository->findTasksOfTeamWithTrello($equipe);
        foreach ($taches as $tache) {
            $trello_api_service->addMemberToBoard($tache->getTrelloboardid(), $email);
        }
        $this->addFlash('success', 'Member added to team successfully!');
        return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
    }

    return $this->render('equipeemploye/new.html.twig', [
        'equipe' => $equipe,
        'form' => $form->createView(),
    ]);
}
#[Route('/equipe/{id_equipe}/remove-member/{id_employe}', name: 'app_equipeemploye_delete', methods: ['POST'])]
public function deleteMember(Request $request, int $id_equipe, Utilisateur $id_employe, EntityManagerInterface $entityManager,TacheRepository $tache_repository,TrelloApiService $trello_api_service): Response
{   $equipe = $entityManager->getRepository(Equipe::class)->find($id_equipe);
    $employe = $entityManager->getRepository(Utilisateur::class)->find($id_employe);
    $equipeEmploye = $entityManager->getRepository(Equipeemploye::class)->findOneBy([
        'equipe' => $equipe,
        'utilisateur' => $employe
    ]);

    if (!$equipeEmploye) {
        throw $this->createNotFoundException('Team member not found');
    }

    if ($this->isCsrfTokenValid('delete'.$id_equipe.$id_employe->getId(), $request->request->get('_token'))) {
        $entityManager->remove($equipeEmploye);
        $entityManager->flush();
        $this->addFlash('success', 'Member removed from team successfully!');
        $taches = $tache_repository->findTasksOfTeamWithTrello($equipe);
        $memberID=$trello_api_service->getMemberIdByEmail($employe->getEmail());
        foreach ($taches as $tache) {
                $trello_api_service->removeMemberFromBoard($tache->getTrelloboardid(), $memberID);
        }
    }

    return $this->redirectToRoute('app_equipe_show', ['id' => $id_equipe]);
}

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
public function delete(
    Request $request, 
    Equipe $equipe, 
    EntityManagerInterface $entityManager,
    TrelloApiService $trello
): Response {
    if (!$this->isCsrfTokenValid('delete'.$equipe->getId(), $request->getPayload()->getString('_token'))) {
        throw $this->createAccessDeniedException('Invalid CSRF token');
    }

    try {
        // Begin transaction for atomic operations
        $entityManager->beginTransaction();

        // Get all projects associated with this team
        $projets = $entityManager->getRepository(Projet::class)
            ->createQueryBuilder('p')
            ->join('p.projetequipes', 'pe')
            ->where('pe.equipe = :equipe')
            ->setParameter('equipe', $equipe)
            ->getQuery()
            ->getResult();

        // Process each project's tasks
        foreach ($projets as $projet) {
            foreach ($projet->getTaches() as $tache) {
                $boardId = $tache->getTrelloboardid();
                if ($boardId) {
                    try {
                        // Delete Trello board
                        $trello->deleteBoard($boardId);
                    } catch (\Exception $e) {
                        // Log error but continue with deletion
                        $this->addFlash('warning', sprintf('Failed to delete Trello board %s: %s', $boardId, $e->getMessage()));
                    }
                    
                    // Clear board ID regardless of Trello deletion success
                    $tache->setTrelloboardid(null);
                    $entityManager->persist($tache);
                }
            }
        }

        // Remove the team
        $entityManager->remove($equipe);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Team deleted successfully');
    } catch (\Exception $e) {
        // Rollback transaction on error
        if ($entityManager->getConnection()->isTransactionActive()) {
            $entityManager->rollback();
        }
        
        $this->addFlash('error', 'Failed to delete team: '.$e->getMessage());
        return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
    }

    return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
}
}
